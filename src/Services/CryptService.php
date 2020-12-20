<?php

namespace AndrewSvirin\Ebics\Services;

use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Factories\Crypt\AESFactory;
use AndrewSvirin\Ebics\Factories\Crypt\RSAFactory;
use AndrewSvirin\Ebics\Factories\OrderDataFactory;
use AndrewSvirin\Ebics\Models\Certificate;
use AndrewSvirin\Ebics\Models\Crypt\AES;
use AndrewSvirin\Ebics\Models\Crypt\RSA;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Models\OrderData;
use AndrewSvirin\Ebics\Models\OrderDataEncrypted;
use RuntimeException;

use function call_user_func_array;
use function strlen;

use const OPENSSL_ZERO_PADDING;

/**
 * EBICS crypt/decrypt encode/decode hash functions.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class CryptService
{

    /**
     * @var RSAFactory
     */
    private $rsaFactory;

    /**
     * @var AESFactory
     */
    private $aesFactory;

    /**
     * @var RandomService
     */
    private $randomService;

    /**
     * @var OrderDataFactory
     */
    private $orderDataFactory;

    public function __construct()
    {
        $this->rsaFactory = new RSAFactory();
        $this->aesFactory = new AESFactory();
        $this->randomService = new RandomService();
        $this->orderDataFactory = new OrderDataFactory();
    }

    /**
     * Calculate hash.
     */
    public function calculateHash(string $text, string $algo = 'sha256'): string
    {
        return hash($algo, $text, true);
    }

    /**
     * Decrypt encrypted OrderData.
     */
    public function decryptOrderData(KeyRing $keyRing, OrderDataEncrypted $orderData): OrderData
    {
        $content = $this->decryptOrderDataContent($keyRing, $orderData);
        $orderData = $this->orderDataFactory->buildOrderDataFromContent($content);

        return $orderData;
    }

    /**
     * Decrypt encrypted OrderData.
     */
    public function decryptOrderDataContent(KeyRing $keyRing, OrderDataEncrypted $orderData): string
    {
        if (!($certificateE = $keyRing->getUserCertificateE())) {
            throw new RuntimeException('Certificate E is not set.');
        }

        $rsa = $this->rsaFactory->create();
        $rsa->setPassword($keyRing->getPassword());
        $rsa->loadKey((string)$certificateE->getPrivateKey());
        $rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
        $transactionKeyDecrypted = $rsa->decrypt($orderData->getTransactionKey());
        // aes-128-cbc encrypting format.
        $aes = $this->aesFactory->create(AES::MODE_CBC);
        $aes->setKeyLength(128);
        $aes->setKey($transactionKeyDecrypted);
        // Force openssl_options.
        $aes->setOpenSSLOptions(OPENSSL_ZERO_PADDING);
        $decrypted = $aes->decrypt($orderData->getOrderData());

        // Try to uncompress from gz order data.
        if (!($orderData = gzuncompress($decrypted))) {
            throw new EbicsException('Order Data were uncompressed wrongly.');
        }
        return $orderData;
    }

    /**
     * Calculate signatureValue by encrypting Signature value with user Private key.
     *
     * @return string Base64 encoded
     *
     * @throws EbicsException
     */
    public function cryptSignatureValue(KeyRing $keyRing, string $hash): string
    {
        $digestToSignBin = $this->filter($hash);

        if (!($certificateX = $keyRing->getUserCertificateX()) || !($privateKey = $certificateX->getPrivateKey())) {
            throw new EbicsException(
                'On this stage must persist certificate for authorization. ' .
                'Run INI and HIA requests for retrieve them.'
            );
        }

        $passphrase = $keyRing->getPassword();
        $rsa = $this->rsaFactory->create();
        $rsa->setPassword($passphrase);
        $rsa->loadKey($privateKey, RSA::PRIVATE_FORMAT_PKCS1);
        if (!defined('CRYPT_RSA_PKCS15_COMPAT')) {
            define('CRYPT_RSA_PKCS15_COMPAT', true);
        }
        $rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
        $encrypted = $rsa->encrypt($digestToSignBin);
        if (empty($encrypted)) {
            throw new EbicsException('Incorrect authorization.');
        }

        return $encrypted;
    }

    /**
     * Generate public and private keys.
     *
     * @return array = [
     *      'publickey' => '<string>',
     *      'privatekey' => '<string>',
     *  ]
     */
    public function generateKeys(KeyRing $keyRing, string $algo = 'sha256', int $length = 2048): array
    {
        $rsa = $this->rsaFactory->create();
        $rsa->setPublicKeyFormat(RSA::PRIVATE_FORMAT_PKCS1);
        $rsa->setPrivateKeyFormat(RSA::PUBLIC_FORMAT_PKCS1);
        $rsa->setHash($algo);
        $rsa->setMGFHash($algo);
        $rsa->setPassword($keyRing->getPassword());

        return $rsa->createKey($length);
    }

    /**
     * Filter hash of blocked characters.
     *
     * @return string
     */
    private function filter(string $hash)
    {
        $RSA_SHA256prefix = [
            0x30,
            0x31,
            0x30,
            0x0D,
            0x06,
            0x09,
            0x60,
            0x86,
            0x48,
            0x01,
            0x65,
            0x03,
            0x04,
            0x02,
            0x01,
            0x05,
            0x00,
            0x04,
            0x20,
        ];
        if (!($unpHash = unpack('C*', $hash))) {
            throw new RuntimeException('Unpack failed.');
        }
        $signedInfoDigest = array_values($unpHash);
        $digestToSign = [];
        $this->systemArrayCopy($RSA_SHA256prefix, 0, $digestToSign, 0, count($RSA_SHA256prefix));
        $this->systemArrayCopy($signedInfoDigest, 0, $digestToSign, count($RSA_SHA256prefix), count($signedInfoDigest));

        return $this->arrayToBin($digestToSign);
    }

    /**
     * System.arrayCopy java function interpretation.
     */
    private function systemArrayCopy(array $a, int $c, array &$b, int $d, int $length): void
    {
        for ($i = 0; $i < $length; ++$i) {
            $b[$i + $d] = $a[$i + $c];
        }
    }

    /**
     * Pack array of bytes to one bytes-string.
     *
     * @param array<int, int> $bytes
     *
     * @return string (bytes)
     */
    private function arrayToBin(array $bytes): string
    {
        return call_user_func_array('pack', array_merge(['c*'], $bytes));
    }

    /**
     * Calculate Public Digest.
     *
     * Try to use certificate public key prioritized.
     *
     * Concat the exponent and modulus (hex representation) with a single whitespace.
     * Remove leading zeros from both.
     * Calculate digest (SHA256).
     *
     * @param Certificate $certificate
     * @param string $algorithm
     *
     * @return string
     */
    public function calculateDigest(Certificate $certificate, $algorithm = 'sha256')
    {
        $publicKey = $this->rsaFactory->create();
        $publicKey->loadKey($certificate->getPublicKey());
        $e0 = $publicKey->getExponent()->toHex(true);
        $m0 = $publicKey->getModulus()->toHex(true);
        // If key was formed incorrect with Modulus and Exponent mismatch, then change the place of key parts.
        if (strlen($e0) > strlen($m0)) {
            $buffer = $e0;
            $e0 = $m0;
            $m0 = $buffer;
        }
        $e1 = ltrim($e0, '0');
        $m1 = ltrim($m0, '0');
        $key1 = sprintf('%s %s', $e1, $m1);

        return hash($algorithm, $key1, true);
    }

    /**
     * generate 16 pseudo bytes.
     *
     * @return string
     */
    public function generateNonce()
    {
        $bytes = $this->randomService->string(16);
        $nonce = bin2hex($bytes);

        return strtoupper($nonce);
    }

    /**
     * Transform public key on exponent and modulus.
     *
     * @param string $publicKey
     *
     * @return array = [
     *   'e' => '<bytes>',
     *   'm' => '<bytes>',
     * ]
     */
    public function getPublicKeyDetails(string $publicKey): array
    {
        $rsa = $this->rsaFactory->create();
        $rsa->setPublicKey($publicKey);

        return [
            'e' => $rsa->getExponent()->toBytes(),
            'm' => $rsa->getModulus()->toBytes(),
        ];
    }
}

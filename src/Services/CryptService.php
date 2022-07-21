<?php

namespace AndrewSvirin\Ebics\Services;

use AndrewSvirin\Ebics\Contracts\Crypt\RSAInterface;
use AndrewSvirin\Ebics\Contracts\SignatureInterface;
use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Factories\Crypt\AESFactory;
use AndrewSvirin\Ebics\Factories\Crypt\RSAFactory;
use AndrewSvirin\Ebics\Models\KeyRing;
use LogicException;
use RuntimeException;

/**
 * EBICS crypt/decrypt encode/decode hash functions.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @internal
 */
final class CryptService
{
    private RSAFactory $rsaFactory;
    private AESFactory $aesFactory;
    private RandomService $randomService;

    public function __construct()
    {
        $this->rsaFactory = new RSAFactory();
        $this->aesFactory = new AESFactory();
        $this->randomService = new RandomService();
    }

    /**
     * Calculate hash.
     *
     * @param string $text
     * @param string $algorithm
     * @param bool $binary
     * @return string
     */
    public function hash(string $text, string $algorithm = 'sha256', bool $binary = true): string
    {
        return hash($algorithm, $text, $binary);
    }

    /**
     * Decrypt encrypted OrderData.
     *
     * @param KeyRing $keyRing
     * @param string $orderDataEncrypted
     * @param string $transactionKey
     *
     * @return string
     * @throws EbicsException
     */
    public function decryptOrderDataCompressed(
        KeyRing $keyRing,
        string $orderDataEncrypted,
        string $transactionKey
    ): string {
        if (!($signatureE = $keyRing->getUserSignatureE())) {
            throw new RuntimeException('Signature E is not set.');
        }

        $rsa = $this->rsaFactory->createPrivate($signatureE->getPrivateKey(), $keyRing->getPassword());
        $transactionKeyDecrypted = $rsa->decrypt($transactionKey);
        $orderDataCompressed = $this->decryptByKey($transactionKeyDecrypted, $orderDataEncrypted);

        return $orderDataCompressed;
    }

    /**
     * Algorithm AES-128-CBC.
     *
     * @param string $key
     * @param string $encrypted
     *
     * @return string
     */
    public function decryptByKey(string $key, string $encrypted): string
    {
        $aes = $this->aesFactory->create();
        $aes->setKeyLength(128);
        $aes->setKey($key);
        // Force openssl_options.
        $aes->setOpenSSLOptions(OPENSSL_ZERO_PADDING);
        $decrypted = $aes->decrypt($encrypted);

        return $decrypted;
    }

    /**
     * Algorithm AES-128-CBC.
     *
     * @param string $key
     * @param string $data
     *
     * @return string
     */
    public function encryptByKey(string $key, string $data): string
    {
        $aes = $this->aesFactory->create();
        $aes->setKeyLength(128);
        $aes->setKey($key);
        $aes->setOpenSSLOptions(OPENSSL_RAW_DATA);
        $encrypted = $aes->encrypt($data);

        return $encrypted;
    }

    /**
     * Calculate signatureValue by encrypting Signature value with user Private key.
     *
     * @param string $privateKey
     * @param string $password
     * @param string $data
     *
     * @return string
     */
    public function encrypt(
        string $privateKey,
        string $password,
        string $data
    ): string {
        $digestToSignBin = $this->filter($data);

        $rsa = $this->rsaFactory->createPrivate($privateKey, $password);

        return $this->encryptByRsa($rsa, $digestToSignBin);
    }

    public function sign(
        string $privateKey,
        string $password,
        string $type,
        string $data
    ): string {
        switch ($type) {
            case 'A005':
                $rsa = $this->rsaFactory->createPrivate($privateKey, $password);
                $rsa->setHash('sha256');
                $sign = $rsa->emsaPkcs1V15Encode($data);
                break;
            case 'A006':
                $rsa = $this->rsaFactory->createPrivate($privateKey, $password);
                $rsa->setHash('sha256');
                $rsa->setMGFHash('sha256');
                $sign = $rsa->emsaPssEncode($data);
                if (!$rsa->emsaPssVerify($data, $sign)) {
                    throw new LogicException('Sign verification failed');
                }
                break;
            default:
                throw new LogicException(sprintf('Algorithm type %s not supported', $type));
        }

        return $sign;
    }

    /**
     * Encrypt transaction key by RSA public key.
     *
     * @param string $publicKey
     * @param string $transactionKey
     *
     * @return string
     */
    public function encryptTransactionKey(string $publicKey, string $transactionKey): string
    {
        return $this->encryptByRsaPublicKey($publicKey, $transactionKey);
    }

    /**
     * Encrypt by private key.
     *
     * @param RSAInterface $rsa
     * @param string $data
     *
     * @return string
     */
    private function encryptByRsa(RSAInterface $rsa, string $data): string
    {
        if (!($encrypted = $rsa->encrypt($data))) {
            throw new RuntimeException('Incorrect encryption.');
        }

        return $encrypted;
    }

    /**
     * Encrypt by public key.
     *
     * @param string $publicKey
     * @param string $data
     *
     * @return string
     */
    private function encryptByRsaPublicKey(string $publicKey, string $data): string
    {
        $rsa = $this->rsaFactory->createPublic($publicKey);

        if (!($encrypted = $rsa->encrypt($data))) {
            throw new RuntimeException('Incorrect encryption.');
        }

        return $encrypted;
    }

    /**
     * Generate public and private keys.
     *
     * @param string $password
     * @param string $algorithm
     * @param int $length
     *
     * @return array = [
     *      'publickey' => '<string>',
     *      'privatekey' => '<string>',
     *  ]
     */
    public function generateKeys(
        string $password,
        string $algorithm = 'sha256',
        int $length = 2048
    ): array {
        $rsa = $this->rsaFactory->create();
        $rsa->setHash($algorithm);
        $rsa->setPassword($password);

        return $rsa->createKey($length);
    }

    /**
     * Filter hash of blocked characters.
     *
     * @param string $hash
     *
     * @return string
     */
    private function filter(string $hash): string
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
        $unpHash = $this->binToArray($hash);
        $signedInfoDigest = array_values($unpHash);
        $digestToSign = [];
        $this->systemArrayCopy($RSA_SHA256prefix, 0, $digestToSign, 0, count($RSA_SHA256prefix));
        $this->systemArrayCopy($signedInfoDigest, 0, $digestToSign, count($RSA_SHA256prefix), count($signedInfoDigest));

        return $this->arrayToBin($digestToSign);
    }

    /**
     * System.arrayCopy java function interpretation.
     *
     * @param array $a
     * @param int $c
     * @param array $b
     * @param int $d
     * @param int $length
     */
    private function systemArrayCopy(
        array $a,
        int $c,
        array &$b,
        int $d,
        int $length
    ): void {
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
    private function arrayToBin(
        array $bytes
    ): string {
        return call_user_func_array('pack', array_merge(['c*'], $bytes));
    }

    /**
     * Convert bytes to array.
     *
     * @param string $bytes
     *
     * @return array
     */
    public function binToArray(
        string $bytes
    ): array {
        $result = unpack('C*', $bytes);
        if (false === $result) {
            throw new RuntimeException('Can not convert bytes to array.');
        }
        return $result;
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
     * @param SignatureInterface $signature
     * @param string $algorithm
     * @param bool $rawOutput
     *
     * @return string
     */
    public function calculateDigest(
        SignatureInterface $signature,
        string $algorithm = 'sha256',
        bool $rawOutput = true
    ): string {
        $rsa = $this->rsaFactory->createPublic($signature->getPublicKey());

        $exponent = $rsa->getExponent()->toHex(true);
        $modulus = $rsa->getModulus()->toHex(true);

        $key = $this->calculateKey($exponent, $modulus);

        return $this->hash($key, $algorithm, $rawOutput);
    }

    /**
     * Make key from exponent and modulus.
     *
     * @param string $exponent
     * @param string $modulus
     *
     * @return string
     */
    public function calculateKey(
        string $exponent,
        string $modulus
    ): string {
        // Remove leading 0.
        $exponent = ltrim($exponent, '0');
        $modulus = ltrim($modulus, '0');

        return sprintf('%s %s', $exponent, $modulus);
    }

    /**
     * Make certificate fingerprint.
     *
     * @param string $key
     * @param string $algorithm
     * @param bool $rawOutput
     * @return string
     */
    public function calculateCertificateFingerprint(
        string $key,
        string $algorithm = 'sha256',
        bool $rawOutput = false
    ): string {
        $fingerprint = openssl_x509_fingerprint($key, $algorithm, $rawOutput);
        if (false === $fingerprint) {
            throw new RuntimeException('Can not calculate fingerprint for certificate.');
        }

        return $fingerprint;
    }


    /**
     * Generate nonce from 32 HEX digits.
     *
     * @return string
     */
    public function generateNonce(): string
    {
        $nonce = $this->randomService->hex(32);

        return $nonce;
    }

    /**
     * Generate transaction key from 16 pseudo bytes.
     *
     * @return string
     */
    public function generateTransactionKey(): string
    {
        $transactionKey = $this->randomService->bytes(16);

        return $transactionKey;
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
        $rsa = $this->rsaFactory->createPublic($publicKey);

        return [
            'e' => $rsa->getExponent()->toBytes(),
            'm' => $rsa->getModulus()->toBytes(),
        ];
    }
}

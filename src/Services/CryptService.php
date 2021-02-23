<?php

namespace AndrewSvirin\Ebics\Services;

use AndrewSvirin\Ebics\Contracts\SignatureInterface;
use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Factories\Crypt\AESFactory;
use AndrewSvirin\Ebics\Factories\Crypt\RSAFactory;
use AndrewSvirin\Ebics\Factories\OrderDataFactory;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Models\OrderData;
use AndrewSvirin\Ebics\Models\OrderDataEncrypted;
use RuntimeException;

/**
 * EBICS crypt/decrypt encode/decode hash functions.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @internal
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

    /**
     * @var ZipService
     */
    private $zipService;

    public function __construct()
    {
        $this->rsaFactory = new RSAFactory();
        $this->aesFactory = new AESFactory();
        $this->randomService = new RandomService();
        $this->orderDataFactory = new OrderDataFactory();
        $this->zipService = new ZipService();
    }

    /**
     * Calculate hash.
     *
     * @param string $text
     * @param string $algorithm
     *
     * @return string
     */
    public function calculateHash(string $text, string $algorithm = 'sha256'): string
    {
        return hash($algorithm, $text, true);
    }

    /**
     * Decrypt encrypted OrderData.
     *
     * @param KeyRing $keyRing
     * @param OrderDataEncrypted $orderDataEncrypted
     *
     * @return OrderData
     * @throws EbicsException
     */
    public function decryptOrderData(KeyRing $keyRing, OrderDataEncrypted $orderDataEncrypted): OrderData
    {
        $orderDataContent = $this->decryptOrderDataContent($keyRing, $orderDataEncrypted);
        $orderData = $this->orderDataFactory->createOrderDataFromContent($orderDataContent);

        return $orderData;
    }

    /**
     * Decrypt encrypted OrderData items.
     * Unzip order data items.
     *
     * @param KeyRing $keyRing
     * @param OrderDataEncrypted $orderData
     *
     * @return OrderData[]
     * @throws EbicsException
     */
    public function decryptOrderDataItems(KeyRing $keyRing, OrderDataEncrypted $orderData): array
    {
        $orderDataContent = $this->decryptOrderDataContent($keyRing, $orderData);

        $orderDataXmlItems = $this->zipService->extractFilesFromString($orderDataContent);

        $orderDataItems = [];
        foreach ($orderDataXmlItems as $orderDataXmlItem) {
            $orderDataItems[] = $this->orderDataFactory->createOrderDataFromContent($orderDataXmlItem);
        }

        return $orderDataItems;
    }

    /**
     * Decrypt encrypted OrderData.
     *
     * @param KeyRing $keyRing
     * @param OrderDataEncrypted $orderData
     *
     * @return string
     * @throws EbicsException
     */
    public function decryptOrderDataContent(
        KeyRing $keyRing,
        OrderDataEncrypted $orderData
    ): string {
        if (!($signatureE = $keyRing->getUserSignatureE())) {
            throw new RuntimeException('Signature E is not set.');
        }

        $rsa = $this->rsaFactory->createPrivate($signatureE->getPrivateKey(), $keyRing->getPassword());
        $transactionKeyDecrypted = $rsa->decrypt($orderData->getTransactionKey());
        $decrypted = $this->decryptByKey($transactionKeyDecrypted, $orderData->getOrderData());
        $orderData = $this->zipService->uncompress($decrypted);

        return $orderData;
    }

    /**
     * Algorithm AES-128-CBC.
     *
     * @param string $key
     * @param string $encrypted
     *
     * @return string
     */
    public function decryptByKey(string $key, string $encrypted)
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
     * @param string $decrypted
     *
     * @return string
     */
    public function encryptByKey(string $key, string $decrypted)
    {
        $aes = $this->aesFactory->create();
        $aes->setKeyLength(128);
        $aes->setKey($key);
        $encrypted = $aes->encrypt($decrypted);

        return $encrypted;
    }

    /**
     * Calculate signatureValue by encrypting Signature value with user Private key.
     *
     * @param string $privateKey
     * @param string $password
     * @param string $hash
     *
     * @return string
     */
    public function encryptSignatureValue(
        string $privateKey,
        string $password,
        string $hash
    ): string {
        $digestToSignBin = $this->filter($hash);

        return $this->encryptByRsaPrivateKey($privateKey, $password, $digestToSignBin);
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
     * @param string $privateKey
     * @param string $password
     * @param string $data
     *
     * @return string
     */
    private function encryptByRsaPrivateKey(string $privateKey, string $password, string $data)
    {
        $rsa = $this->rsaFactory->createPrivate($privateKey, $password);

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
        $algorithm = 'sha256',
        $rawOutput = false
    ): string {
        $rsa = $this->rsaFactory->createPublic($signature->getPublicKey());

        $exponent = $rsa->getExponent()->toHex(true);
        $modulus = $rsa->getModulus()->toHex(true);

        $key = $this->calculateKey($exponent, $modulus);

        return $this->calculateKeyHash($key, $algorithm, $rawOutput);
    }

    /**
     * Make key from  exponent and modulus.
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
     * Make key hash.
     *
     * @param string $key
     * @param string $algorithm
     * @param bool $rawOutput
     *
     * @return string
     */
    public function calculateKeyHash(
        string $key,
        string $algorithm = 'sha256',
        bool $rawOutput = false
    ): string {
        return hash($algorithm, $key, $rawOutput);
    }

    /**
     * generate 16 pseudo bytes.
     *
     * @return string
     */
    public function generateNonce(): string
    {
        $nonce = $this->randomService->hex(32);

        return $nonce;
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

<?php

namespace AndrewSvirin\Ebics\services;

use AndrewSvirin\Ebics\exceptions\EbicsException;
use AndrewSvirin\Ebics\factories\OrderDataFactory;
use AndrewSvirin\Ebics\models\KeyRing;
use AndrewSvirin\Ebics\models\OrderData;
use AndrewSvirin\Ebics\models\OrderDataEncrypted;
use phpseclib\Crypt\AES;
use phpseclib\Crypt\RSA;

/**
 * EBICS crypt/decrypt encode/decode hash functions.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class CryptService
{

   /**
    * @var KeyRing
    */
   private $keyRing;

   public function __construct(KeyRing $keyRing)
   {
      $this->keyRing = $keyRing;
   }

   /**
    * Calculate hash.
    * @param string $text
    * @param string $algo
    * @return string
    */
   public function calculateHash(string $text, $algo = 'SHA256'): string
   {
      return hash($algo, $text, true);
   }

   /**
    * Decrypt encrypted OrDerData.
    * @param OrderDataEncrypted $orderData
    * @return OrderData
    */
   public function decryptOrderData(OrderDataEncrypted $orderData): OrderData
   {
      $rsa = new RSA();
      $rsa->setPassword($this->keyRing->getPassword());
      $rsa->loadKey($this->keyRing->getUserCertificateE()->getKeys()['privatekey']);
      $rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
      $transactionIdDecrypted = $rsa->decrypt($orderData->getTransactionId());
      // aes-128-cbc encrypting format.
      $aes = new AES(AES::MODE_CBC);
      $aes->setKeyLength(128);
      $aes->setKey($transactionIdDecrypted);
      // Force openssl_options.
      $aes->openssl_options = OPENSSL_ZERO_PADDING;
      $decrypted = $aes->decrypt($orderData->getOrderData());
      $content = gzuncompress($decrypted);
      $orderData = OrderDataFactory::buildOrderDataFromContent($content);
      return $orderData;
   }

   /**
    * Calculate signatureValue by encrypting Signature value with user Private key.
    * @param string $hash
    * @return string Base64 encoded
    * @throws EbicsException
    */
   public function cryptSignatureValue(string $hash): string
   {
      $digestToSignBin = $this->filter($hash);
      $privateKey = $this->keyRing->getUserCertificateX()->getKeys()['privatekey'];
      $passphrase = $this->keyRing->getPassword();
      $rsa = new RSA();
      $rsa->setPassword($passphrase);
      $rsa->loadKey($privateKey, RSA::PRIVATE_FORMAT_PKCS1);
      define('CRYPT_RSA_PKCS15_COMPAT', true);
      $rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
      $encrypted = $rsa->encrypt($digestToSignBin);
      if (empty($encrypted))
      {
         throw new EbicsException('Incorrect authorization.');
      }
      return $encrypted;
   }

   /**
    * Filter hash of blocked characters.
    * @param string $hash
    * @return string
    */
   private function filter(string $hash)
   {
      $RSA_SHA256prefix = [
         0x30, 0x31, 0x30, 0x0D, 0x06, 0x09, 0x60, 0x86, 0x48, 0x01, 0x65, 0x03, 0x04, 0x02, 0x01, 0x05, 0x00, 0x04, 0x20,
      ];
      $signedInfoDigest = array_values(unpack('C*', $hash));
      $digestToSign = [];
      $this->systemArrayCopy($RSA_SHA256prefix, 0, $digestToSign, 0, count($RSA_SHA256prefix));
      $this->systemArrayCopy($signedInfoDigest, 0, $digestToSign, count($RSA_SHA256prefix), count($signedInfoDigest));
      $digestToSignBin = $this->arrayToBin($digestToSign);
      return $digestToSignBin;
   }

   /**
    * System.arrayCopy java function interpretation.
    * @param array $a
    * @param integer $c
    * @param array $b
    * @param integer $d
    * @param integer $length
    */
   private function systemArrayCopy(array $a, int $c, array &$b, int $d, int $length): void
   {
      for ($i = 0; $i < $length; $i++)
      {
         $b[$i + $d] = $a[$i + $c];
      }
   }

   /**
    * Pack array of bytes to one bytes-string.
    * @param array $bytes
    * @return string (bytes)
    */
   private function arrayToBin(array $bytes): string
   {
      return call_user_func_array('pack', array_merge(['c*'], $bytes));
   }

   /**
    * Calculate Public Digest
    *
    * Concat the exponent and modulus (hex representation) with a single whitespace
    * remove leading zeros from both
    * calculate digest (SHA256)
    * encode as Base64
    *
    * @param integer $exponent
    * @param integer $modulus
    * @return string
    */
   public function calculateDigest($exponent, $modulus)
   {
      $e = ltrim((string)$exponent, '0');
      $m = ltrim((string)$modulus, '0');
      $concat = $e . ' ' . $m;
      $sha256 = hash('sha256', $concat, TRUE);
      $b64en = base64_encode($sha256);
      return $b64en;
   }

}
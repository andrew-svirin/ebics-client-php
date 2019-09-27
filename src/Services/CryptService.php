<?php

namespace AndrewSvirin\Ebics\Services;

use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Factories\OrderDataFactory;
use AndrewSvirin\Ebics\Models\Certificate;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Models\OrderData;
use AndrewSvirin\Ebics\Models\OrderDataEncrypted;
use phpseclib\Crypt\AES;
use phpseclib\Crypt\Random;
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
    * Calculate hash.
    * @param string $text
    * @param string $algo
    * @return string
    */
   public static function calculateHash(string $text, $algo = 'sha256'): string
   {
      return hash($algo, $text, true);
   }

   /**
    * Decrypt encrypted OrDerData.
    * @param KeyRing $keyRing
    * @param OrderDataEncrypted $orderData
    * @return OrderData
    */
   public static function decryptOrderData(KeyRing $keyRing, OrderDataEncrypted $orderData): OrderData
   {
      $rsa = new RSA();
      $rsa->setPassword($keyRing->getPassword());
      $rsa->loadKey($keyRing->getUserCertificateE()->getPrivateKey());
      $rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
      $transactionKeyDecrypted = $rsa->decrypt($orderData->getTransactionKey());
      // aes-128-cbc encrypting format.
      $aes = new AES(AES::MODE_CBC);
      $aes->setKeyLength(128);
      $aes->setKey($transactionKeyDecrypted);
      // Force openssl_options.
      $aes->openssl_options = OPENSSL_ZERO_PADDING;
      $decrypted = $aes->decrypt($orderData->getOrderData());
      $content = gzuncompress($decrypted);
      $orderData = OrderDataFactory::buildOrderDataFromContent($content);
      return $orderData;
   }

   /**
    * Calculate signatureValue by encrypting Signature value with user Private key.
    * @param KeyRing $keyRing
    * @param string $hash
    * @return string Base64 encoded
    * @throws EbicsException
    */
   public static function cryptSignatureValue(KeyRing $keyRing, string $hash): string
   {
      $digestToSignBin = self::filter($hash);
      if (!($certificateX = $keyRing->getUserCertificateX()))
      {
         trigger_error('On this stage must persist certificate for authorization. Run INI and HIA requests for retrieve them.');
      }
      $privateKey = $keyRing->getUserCertificateX()->getPrivateKey();
      $passphrase = $keyRing->getPassword();
      $rsa = new RSA();
      $rsa->setPassword($passphrase);
      $rsa->loadKey($privateKey, RSA::PRIVATE_FORMAT_PKCS1);
      if (!defined('CRYPT_RSA_PKCS15_COMPAT'))
      {
         define('CRYPT_RSA_PKCS15_COMPAT', true);
      }
      $rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
      $encrypted = $rsa->encrypt($digestToSignBin);
      if (empty($encrypted))
      {
         throw new EbicsException('Incorrect authorization.');
      }
      return $encrypted;
   }

   /**
    * Generate public and private keys.
    * @param KeyRing $keyRing
    * @param string $algo
    * @param int $length
    * @return array [
    *    'publickey' => '<string>',
    *    'privatekey' => '<string>',
    * ]
    */
   public static function generateKeys(KeyRing $keyRing, $algo = 'sha256', $length = 2048): array
   {
      $rsa = new RSA();
      $rsa->setPublicKeyFormat(RSA::PRIVATE_FORMAT_PKCS1);
      $rsa->setPrivateKeyFormat(RSA::PUBLIC_FORMAT_PKCS1);
      $rsa->setHash($algo);
      $rsa->setMGFHash($algo);
      $rsa->setPassword($keyRing->getPassword());
      $keys = $rsa->createKey($length);
      return $keys;
   }

   /**
    * Filter hash of blocked characters.
    * @param string $hash
    * @return string
    */
   private static function filter(string $hash)
   {
      $RSA_SHA256prefix = [
         0x30, 0x31, 0x30, 0x0D, 0x06, 0x09, 0x60, 0x86, 0x48, 0x01, 0x65, 0x03, 0x04, 0x02, 0x01, 0x05, 0x00, 0x04, 0x20,
      ];
      $signedInfoDigest = array_values(unpack('C*', $hash));
      $digestToSign = [];
      self::systemArrayCopy($RSA_SHA256prefix, 0, $digestToSign, 0, count($RSA_SHA256prefix));
      self::systemArrayCopy($signedInfoDigest, 0, $digestToSign, count($RSA_SHA256prefix), count($signedInfoDigest));
      $digestToSignBin = self::arrayToBin($digestToSign);
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
   private static function systemArrayCopy(array $a, int $c, array &$b, int $d, int $length): void
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
   private static function arrayToBin(array $bytes): string
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
    * @return string
    */
   public static function calculateDigest(Certificate $certificate, $algorithm = 'sha256')
   {
      $publicKey = new RSA();
      $publicKey->loadKey($certificate->getPublicKey());
      $e0 = $publicKey->exponent->toHex(true);
      $m0 = $publicKey->modulus->toHex(true);
      // If key was formed incorrect with Modulus and Exponent mismatch, then change the place of key parts.
      if (strlen($e0) > strlen($m0))
      {
         $buffer = $e0;
         $e0 = $m0;
         $m0 = $buffer;
      }
      $e1 = ltrim($e0, '0');
      $m1 = ltrim($m0, '0');
      $key1 = sprintf('%s %s', $e1, $m1);
      $hash = hash($algorithm, $key1, true);
      return $hash;
   }

   /**
    * generate 16 pseudo bytes.
    * @return string
    */
   public static function generateNonce()
   {
      $bytes = Random::string(16);
      $nonce = bin2hex($bytes);
      $nonceUpper = strtoupper($nonce);
      return $nonceUpper;
   }

   /**
    * Transform public key on exponent and modulus.
    * @param string $publicKey
    * @return array [
    *    'e' => <bytes>,
    *    'm' => <bytes>,
    * ]
    */
   public static function getPublicKeyDetails(string $publicKey): array
   {
      $rsa = new RSA();
      $rsa->setPublicKey($publicKey);
      return [
         'e' => $rsa->exponent->toBytes(),
         'm' => $rsa->modulus->toBytes(),
      ];
   }

}
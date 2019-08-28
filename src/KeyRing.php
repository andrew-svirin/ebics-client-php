<?php

namespace AndrewSvirin\Ebics;

use AndrewSvirin\Ebics\exceptions\EbicsException;
use AndrewSvirin\Ebics\models\Certificate;
use phpseclib\File\X509;

/**
 * EBICS key ring representation.
 *
 * An EbicsKeyRing instance can hold sets of private user keys and/or public
 * bank keys. Private user keys are always stored AES encrypted by the
 * specified passphrase (derivated by PBKDF2). For each key file on disk or
 * same key dictionary a singleton instance is created.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class KeyRing
{

   /**
    * The path to a key file.
    * @var string
    */
   private $keyRingRealPath;

   /**
    * The passphrase by which all private keys are encrypted/decrypted.
    * @var string
    */
   private $passphrase;

   /**
    * Extracted from file keys.
    * @var array
    */
   private $extractedKeys = [];

   /**
    * @var string
    */
   private $certificateContent;

   /**
    * Constructor.
    * @param string $keyRingRealPath
    * @param string $passphrase
    * @param string $certificateContent
    */
   public function __construct($keyRingRealPath, $passphrase, $certificateContent = null)
   {
      $this->keyRingRealPath = $keyRingRealPath;
      $this->passphrase = $passphrase;
      $this->certificateContent = $certificateContent;
//      $this->extractKeys();
   }

   /**
    * Extract keys.
    * @throws EbicsException
    */
   private function extractKeys()
   {
      $keysRawData = file_get_contents($this->keyRingRealPath);
      if (!$keysRawData)
      {
         throw new EbicsException('Incorrect file path for keys.');
      }
      $keysData = json_decode($keysRawData, true);
      if (!$keysData)
      {
         throw new EbicsException('Can\'t decode key data.');
      }
      $this->extractedKeys = $keysData;
   }

   /**
    * Password phrase.
    * @return string
    */
   public function getPassphrase()
   {
      return $this->passphrase;
   }

   /**
    * Getter for {keys}.
    * @return array
    */
   public function getKeyRingRealPath()
   {
      return $this->extractedKeys;
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
   public static function calculatePublicDigest($exponent, $modulus)
   {
      $e = ltrim((string)$exponent, '0');
      $m = ltrim((string)$modulus, '0');
      $concat = $e . ' ' . $m;
      $sha256 = hash('sha256', $concat, TRUE);
      $b64en = base64_encode($sha256);
      return $b64en;
   }

   /**
    * Format key.
    *
    * @param string $key
    * @param string $type ('PUBLIC'|'PRIVATE')
    * @param string $iv (for 'PRIVATE')
    *
    * @return string
    */
   public static function formatKey($key, $type, $iv = NULL)
   {
      switch ($type)
      {
         case 'PUBLIC':
            $prefix = "-----BEGIN PUBLIC KEY-----\n";
            $suffix = "-----END PUBLIC KEY-----";
            break;
         case 'PRIVATE':
            $prefix = "-----BEGIN PRIVATE KEY-----\nProc-Type: 4,ENCRYPTED\nDEK-Info: DES-EDE3-CBC,{$iv}\n\n";
            $suffix = "-----END PRIVATE KEY-----";
            break;
      }
      $key = str_replace(' ', '', $key);
      $formattedKey = $prefix . chunk_split($key, 64, "\n") . $suffix;

      return $formattedKey;
   }

   /**
    * @return array Certificate.
    */
   public function getCertificateData()
   {
      $x509 = new X509();
      $x509->loadX509($this->certificateContent);
      $certificateData = $x509->currentCert;
      return $certificateData;
   }

   /**
    * @return false|string
    */
   public function getCertificateContent()
   {
      $content = $this->certificateContent;
      return $content;
   }

   private function saveCertificateA(Certificate $certificate): bool
   {

   }

   private function saveCertificateE(Certificate $certificate): bool
   {

   }

   private function saveCertificateX(Certificate $certificate): bool
   {

   }

   public function getCertificateA(): Certificate
   {

   }

   public function getCertificateE(): Certificate
   {

   }

   public function getCertificateX(): Certificate
   {

   }

}

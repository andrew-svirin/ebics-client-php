<?php

namespace AndrewSvirin\Ebics\models;

/**
 * EBICS key ring representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class KeyRing
{

   /**
    * @var string
    */
   private $userId;

   /**
    * @var Certificate
    */
   private $certificateA;

   /**
    * @var Certificate
    */
   private $certificateX;

   /**
    * @var Certificate
    */
   private $certificateE;

   public function setUserId(string $userId)
   {
      $this->userId = $userId;
   }

   public function setCertificateA(Certificate $certificate)
   {
      $this->certificateA = $certificate;
   }

   public function getCertificateA(): ?Certificate
   {
      return $this->certificateA;
   }

   public function setCertificateX(Certificate $certificate)
   {
      $this->certificateX = $certificate;
   }

   public function getCertificateX(): ?Certificate
   {
      return $this->certificateX;
   }

   public function setCertificateE(Certificate $certificate)
   {
      $this->certificateE = $certificate;
   }

   public function getCertificateE(): ?Certificate
   {
      return $this->certificateE;
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

}

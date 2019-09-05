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

   public function getPassword()
   {
      return 'some_secret';
   }

}

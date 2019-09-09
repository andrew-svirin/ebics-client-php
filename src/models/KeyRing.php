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
   private $userCertificateA;

   /**
    * @var Certificate
    */
   private $userCertificateX;

   /**
    * @var Certificate
    */
   private $userCertificateE;

   /**
    * @var Certificate
    */
   private $bankCertificateX;

   /**
    * @var Certificate
    */
   private $bankCertificateE;

   public function setUserId(string $userId)
   {
      $this->userId = $userId;
   }

   public function setUserCertificateA(Certificate $certificate)
   {
      $this->userCertificateA = $certificate;
   }

   public function getUserCertificateA(): ?Certificate
   {
      return $this->userCertificateA;
   }

   public function setUserCertificateX(Certificate $certificate)
   {
      $this->userCertificateX = $certificate;
   }

   public function getUserCertificateX(): ?Certificate
   {
      return $this->userCertificateX;
   }

   public function setUserCertificateE(Certificate $certificate)
   {
      $this->userCertificateE = $certificate;
   }

   public function getUserCertificateE(): ?Certificate
   {
      return $this->userCertificateE;
   }

   public function getPassword()
   {
      return 'some_secret';
   }

}

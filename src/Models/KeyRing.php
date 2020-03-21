<?php

namespace AndrewSvirin\Ebics\Models;

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

   /**
    * @var string
    */
   private $password;

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

   public function getUserCertificateAVersion(): string
   {
      return 'A006';
   }

   public function setUserCertificateX(Certificate $certificate)
   {
      $this->userCertificateX = $certificate;
   }

   public function getUserCertificateX(): ?Certificate
   {
      return $this->userCertificateX;
   }

   public function getUserCertificateXVersion(): string
   {
      return 'X002';
   }

   public function setUserCertificateE(Certificate $certificate)
   {
      $this->userCertificateE = $certificate;
   }

   public function getUserCertificateE(): ?Certificate
   {
      return $this->userCertificateE;
   }

   public function getUserCertificateEVersion(): string
   {
      return 'E002';
   }

   public function setPassword(string $password)
   {
      $this->password = $password;
   }

   public function getPassword(): string
   {
      return $this->password;
   }

   /**
    * @param Certificate $bankCertificateX
    */
   public function setBankCertificateX(Certificate $bankCertificateX): void
   {
      $this->bankCertificateX = $bankCertificateX;
   }

   /**
    * @return Certificate
    */
   public function getBankCertificateX(): ?Certificate
   {
      return $this->bankCertificateX;
   }

   public function getBankCertificateXVersion(): string
   {
      return 'X002';
   }

   /**
    * @param Certificate $bankCertificateE
    */
   public function setBankCertificateE(Certificate $bankCertificateE): void
   {
      $this->bankCertificateE = $bankCertificateE;
   }

   /**
    * @return Certificate
    */
   public function getBankCertificateE(): ?Certificate
   {
      return $this->bankCertificateE;
   }

   public function getBankCertificateEVersion(): string
   {
      return 'E002';
   }

}

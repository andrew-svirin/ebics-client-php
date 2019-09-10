<?php

namespace AndrewSvirin\Ebics\factories;

use AndrewSvirin\Ebics\models\KeyRing;

/**
 * Class KeyRingFactory represents producers for the @see KeyRing.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class KeyRingFactory
{

   const USER_PREFIX = 'USER';
   const BANK_PREFIX = 'BANK';
   const CERTIFICATE_PREFIX_A = 'A';
   const CERTIFICATE_PREFIX_X = 'X';
   const CERTIFICATE_PREFIX_E = 'E';
   const CERTIFICATE_PREFIX = 'CERTIFICATE';
   const PUBLIC_KEY_PREFIX = 'PUBLIC_KEY';
   const PRIVATE_KEY_PREFIX = 'PRIVATE_KEY';

   public static function buildKeyRingFromData(array $data): KeyRing
   {
      $keyRing = new KeyRing();
      if (!empty($data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_A][self::CERTIFICATE_PREFIX]))
      {
         $userCertificateAContent = $data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_A][self::CERTIFICATE_PREFIX];
         $userCertificateAPublicKey = $data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_A][self::PUBLIC_KEY_PREFIX];
         $userCertificateAPrivateKey = $data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_A][self::PRIVATE_KEY_PREFIX];
         $userCertificateA = CertificateFactory::buildCertificateA($userCertificateAContent, $userCertificateAPublicKey, $userCertificateAPrivateKey);
         $keyRing->setUserCertificateA($userCertificateA);
      }
      if (!empty($data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_E][self::CERTIFICATE_PREFIX]))
      {
         $userCertificateEContent = $data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_E][self::CERTIFICATE_PREFIX];
         $userCertificateEPublicKey = $data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_E][self::PUBLIC_KEY_PREFIX];
         $userCertificateEPrivateKey = $data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_E][self::PRIVATE_KEY_PREFIX];
         $userCertificateE = CertificateFactory::buildCertificateE($userCertificateEContent, $userCertificateEPublicKey, $userCertificateEPrivateKey);
         $keyRing->setUserCertificateE($userCertificateE);
      }
      if (!empty($data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_X][self::CERTIFICATE_PREFIX]))
      {
         $userCertificateXContent = $data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_X][self::CERTIFICATE_PREFIX];
         $userCertificateXPublicKey = $data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_X][self::PUBLIC_KEY_PREFIX];
         $userCertificateXPrivateKey = $data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_X][self::PRIVATE_KEY_PREFIX];
         $userCertificateX = CertificateFactory::buildCertificateX($userCertificateXContent, $userCertificateXPublicKey, $userCertificateXPrivateKey);
         $keyRing->setUserCertificateX($userCertificateX);
      }
      if (!empty($data[self::BANK_PREFIX][self::CERTIFICATE_PREFIX_E][self::CERTIFICATE_PREFIX]))
      {
         $bankCertificateEContent = $data[self::BANK_PREFIX][self::CERTIFICATE_PREFIX_E][self::CERTIFICATE_PREFIX];
         $bankCertificateEPublicKey = $data[self::BANK_PREFIX][self::CERTIFICATE_PREFIX_E][self::PUBLIC_KEY_PREFIX];
         $bankCertificateEPrivateKey = $data[self::BANK_PREFIX][self::CERTIFICATE_PREFIX_E][self::PRIVATE_KEY_PREFIX];
         $bankCertificateE = CertificateFactory::buildCertificateE($bankCertificateEContent, $bankCertificateEPublicKey, $bankCertificateEPrivateKey);
         $keyRing->setBankCertificateE($bankCertificateE);
      }
      if (!empty($data[self::BANK_PREFIX][self::CERTIFICATE_PREFIX_X][self::CERTIFICATE_PREFIX]))
      {
         $bankCertificateXContent = $data[self::BANK_PREFIX][self::CERTIFICATE_PREFIX_X][self::CERTIFICATE_PREFIX];
         $bankCertificateXPublicKey = $data[self::BANK_PREFIX][self::CERTIFICATE_PREFIX_X][self::PUBLIC_KEY_PREFIX];
         $bankCertificateXPrivateKey = $data[self::BANK_PREFIX][self::CERTIFICATE_PREFIX_X][self::PRIVATE_KEY_PREFIX];
         $bankCertificateX = CertificateFactory::buildCertificateX($bankCertificateXContent, $bankCertificateXPublicKey, $bankCertificateXPrivateKey);
         $keyRing->setBankCertificateX($bankCertificateX);
      }
      return $keyRing;
   }

   public static function buildDataFromKeyRing(KeyRing $keyRing): array
   {
      if (null !== $keyRing->getUserCertificateA())
      {
         $userCertificateAB64 = base64_encode($keyRing->getUserCertificateA()->getContent());
         $userCertificateAPublicKey = $keyRing->getUserCertificateA()->getPublicKey();
         $userCertificateAPrivateKey = $keyRing->getUserCertificateA()->getPrivateKey();
      }
      if (null !== $keyRing->getUserCertificateE())
      {
         $userCertificateEB64 = base64_encode($keyRing->getUserCertificateE()->getContent());
         $userCertificateEPublicKey = $keyRing->getUserCertificateE()->getPublicKey();
         $userCertificateEPrivateKey = $keyRing->getUserCertificateE()->getPrivateKey();
      }
      if (null !== $keyRing->getUserCertificateX())
      {
         $userCertificateXB64 = base64_encode($keyRing->getUserCertificateX()->getContent());
         $userCertificateXPublicKey = $keyRing->getUserCertificateX()->getPublicKey();
         $userCertificateXPrivateKey = $keyRing->getUserCertificateX()->getPrivateKey();
      }
      if (null !== $keyRing->getBankCertificateE())
      {
         $bankCertificateEB64 = base64_encode($keyRing->getBankCertificateE()->getContent());
         $bankCertificateEPublicKey = $keyRing->getBankCertificateE()->getPublicKey();
         $bankCertificateEPrivateKey = $keyRing->getBankCertificateE()->getPrivateKey();
      }
      if (null !== $keyRing->getBankCertificateX())
      {
         $bankCertificateXB64 = base64_encode($keyRing->getBankCertificateX()->getContent());
         $bankCertificateXPublicKey = $keyRing->getBankCertificateX()->getPublicKey();
         $bankCertificateXPrivateKey = $keyRing->getBankCertificateX()->getPrivateKey();
      }
      return [
         self::USER_PREFIX => [
            self::CERTIFICATE_PREFIX_A => [
               self::CERTIFICATE_PREFIX => isset($userCertificateAB64) ? $userCertificateAB64 : null,
               self::PUBLIC_KEY_PREFIX => isset($userCertificateAPublicKey) ? $userCertificateAPublicKey : null,
               self::PRIVATE_KEY_PREFIX => isset($userCertificateAPrivateKey) ? $userCertificateAPrivateKey : null,
            ],
            self::CERTIFICATE_PREFIX_E => [
               self::CERTIFICATE_PREFIX => isset($userCertificateEB64) ? $userCertificateEB64 : null,
               self::PUBLIC_KEY_PREFIX => isset($userCertificateEPublicKey) ? $userCertificateEPublicKey : null,
               self::PRIVATE_KEY_PREFIX => isset($userCertificateEPrivateKey) ? $userCertificateEPrivateKey : null,
            ],
            self::CERTIFICATE_PREFIX_X => [
               self::CERTIFICATE_PREFIX => isset($userCertificateXB64) ? $userCertificateXB64 : null,
               self::PUBLIC_KEY_PREFIX => isset($userCertificateXPublicKey) ? $userCertificateXPublicKey : null,
               self::PRIVATE_KEY_PREFIX => isset($userCertificateXPrivateKey) ? $userCertificateXPrivateKey : null,
            ],
         ],
         self::BANK_PREFIX => [
            self::CERTIFICATE_PREFIX_E => [
               self::CERTIFICATE_PREFIX => isset($bankCertificateEB64) ? $bankCertificateEB64 : null,
               self::PUBLIC_KEY_PREFIX => isset($bankCertificateEPublicKey) ? $bankCertificateEPublicKey : null,
               self::PRIVATE_KEY_PREFIX => isset($bankCertificateEPrivateKey) ? $bankCertificateEPrivateKey : null,
            ],
            self::CERTIFICATE_PREFIX_X => [
               self::CERTIFICATE_PREFIX => isset($bankCertificateXB64) ? $bankCertificateXB64 : null,
               self::PUBLIC_KEY_PREFIX => isset($bankCertificateXPublicKey) ? $bankCertificateXPublicKey : null,
               self::PRIVATE_KEY_PREFIX => isset($bankCertificateXPrivateKey) ? $bankCertificateXPrivateKey : null,
            ],
         ],
      ];
   }

}
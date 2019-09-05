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
   const CERTIFICATE_PREFIX_A = 'A';
   const CERTIFICATE_PREFIX_X = 'X';
   const CERTIFICATE_PREFIX_E = 'E';
   const CERTIFICATE_PREFIX = 'CERTIFICATE';
   const KEYS_PREFIX = 'KEYS';

   public static function buildKeyRingFromData(array $data): KeyRing
   {
      $keyRing = new KeyRing();
      if (!empty($data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_A][self::CERTIFICATE_PREFIX]))
      {
         $certificateAContent = $data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_A][self::CERTIFICATE_PREFIX];
         $certificateAKeys = $data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_A][self::KEYS_PREFIX];
         $certificateA = CertificateFactory::buildCertificateA($certificateAContent, $certificateAKeys);
         $keyRing->setCertificateA($certificateA);
      }
      if (!empty($data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_E][self::CERTIFICATE_PREFIX]))
      {
         $certificateEContent = $data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_E][self::CERTIFICATE_PREFIX];
         $certificateEKeys = $data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_E][self::KEYS_PREFIX];
         $certificateE = CertificateFactory::buildCertificateE($certificateEContent, $certificateEKeys);
         $keyRing->setCertificateE($certificateE);
      }
      if (!empty($data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_X][self::CERTIFICATE_PREFIX]))
      {
         $certificateXContent = $data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_X][self::CERTIFICATE_PREFIX];
         $certificateXKeys = $data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_X][self::KEYS_PREFIX];
         $certificateX = CertificateFactory::buildCertificateX($certificateXContent, $certificateXKeys);
         $keyRing->setCertificateX($certificateX);
      }
      return $keyRing;
   }

   public static function buildDataFromKeyRing(KeyRing $keyRing): array
   {
      if (null !== $keyRing->getCertificateA())
      {
         $certificateAB64 = base64_encode($keyRing->getCertificateA()->getContent());
         $certificateAKeys = $keyRing->getCertificateA()->getKeys();
      }
      if (null !== $keyRing->getCertificateE())
      {
         $certificateEB64 = base64_encode($keyRing->getCertificateE()->getContent());
         $certificateEKeys = $keyRing->getCertificateE()->getKeys();
      }
      if (null !== $keyRing->getCertificateX())
      {
         $certificateXB64 = base64_encode($keyRing->getCertificateX()->getContent());
         $certificateXKeys = $keyRing->getCertificateX()->getKeys();
      }
      return [
         self::USER_PREFIX => [
            self::CERTIFICATE_PREFIX_A => [
               self::CERTIFICATE_PREFIX => isset($certificateAB64) ? $certificateAB64 : null,
               self::KEYS_PREFIX => isset($certificateAKeys) ? $certificateAKeys : null,
            ],
            self::CERTIFICATE_PREFIX_E => [
               self::CERTIFICATE_PREFIX => isset($certificateEB64) ? $certificateEB64 : null,
               self::KEYS_PREFIX => isset($certificateEKeys) ? $certificateEKeys : null,
            ],
            self::CERTIFICATE_PREFIX_X => [
               self::CERTIFICATE_PREFIX => isset($certificateXB64) ? $certificateXB64 : null,
               self::KEYS_PREFIX => isset($certificateXKeys) ? $certificateXKeys : null,
            ],
         ],
      ];
   }

}
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

   public static function buildKeyRingFromData(array $data): KeyRing
   {
      $keyRing = new KeyRing();
      if (isset($data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_A]))
      {
         $certificateA = CertificateFactory::buildCertificateA($data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_A]);
         $keyRing->setCertificateA($certificateA);
      }
      if (isset($data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_E]))
      {
         $certificateE = CertificateFactory::buildCertificateE($data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_E]);
         $keyRing->setCertificateE($certificateE);
      }
      if (isset($data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_X]))
      {
         $certificateX = CertificateFactory::buildCertificateX($data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_X]);
         $keyRing->setCertificateX($certificateX);
      }
      return $keyRing;
   }

   public static function buildDataFromKeyRing(KeyRing $keyRing): array
   {
      if (null !== $keyRing->getCertificateA())
      {
         $certificateAB64 = base64_encode($keyRing->getCertificateA()->getContent());
      }
      if (null !== $keyRing->getCertificateE())
      {
         $certificateEB64 = base64_encode($keyRing->getCertificateE()->getContent());
      }
      if (null !== $keyRing->getCertificateX())
      {
         $certificateXB64 = base64_encode($keyRing->getCertificateX()->getContent());
      }
      return [
         self::USER_PREFIX => [
            self::CERTIFICATE_PREFIX_A => isset($certificateAB64) ? $certificateAB64 : null,
            self::CERTIFICATE_PREFIX_E => isset($certificateEB64) ? $certificateEB64 : null,
            self::CERTIFICATE_PREFIX_X => isset($certificateXB64) ? $certificateXB64 : null,
         ],
      ];
   }

}
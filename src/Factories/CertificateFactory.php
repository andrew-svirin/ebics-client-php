<?php

namespace AndrewSvirin\Ebics\Factories;

use AndrewSvirin\Ebics\Models\Certificate;
use DateTime;
use phpseclib\Crypt\RSA;
use phpseclib\File\X509;
use phpseclib\Math\BigInteger;

/**
 * Class CertificateFactory represents producers for the @see Certificate.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class CertificateFactory
{

   public static function buildCertificateA(string $publicKey, string $privateKey, string $content = null): Certificate
   {
      return new Certificate(Certificate::TYPE_A, $publicKey, $privateKey, $content);
   }

   public static function buildCertificateE(string $publicKey, string $privateKey = null, string $content = null): Certificate
   {
      return new Certificate(Certificate::TYPE_E, $publicKey, $privateKey, $content);
   }

   public static function buildCertificateX(string $publicKey, string $privateKey = null, string $content = null): Certificate
   {
      return new Certificate(Certificate::TYPE_X, $publicKey, $privateKey, $content);
   }

   public static function generateCertificateAFromKeys(array $keys, bool $isCertified): Certificate
   {
      return self::generateCertificateFromKeys($keys, Certificate::TYPE_A, $isCertified);
   }

   public static function generateCertificateEFromKeys(array $keys, bool $isCertified): Certificate
   {
      return self::generateCertificateFromKeys($keys, Certificate::TYPE_E, $isCertified);
   }

   public static function generateCertificateXFromKeys(array $keys, bool $isCertified): Certificate
   {
      return self::generateCertificateFromKeys($keys, Certificate::TYPE_X, $isCertified);
   }

   private static function generateCertificateFromKeys(array $keys, string $type, bool $isCertified): Certificate
   {
      if ($isCertified)
      {
         $certificateContent = self::generateCertificateContent($keys);
      }
      $certificate = new Certificate($type, $keys['publickey'], $keys['privatekey'], $certificateContent ?? null);
      return $certificate;
   }

   private static function generateCertificateContent(array $keys): string
   {
      // TODO: Move options to separate settings.
      $privateKey = new RSA();
      $privateKey->loadKey($keys['privatekey']);

      $publicKey = new RSA();
      $publicKey->loadKey($keys['publickey']);
      $publicKey->setPublicKey();

      $subject = new X509();
      $subject->setPublicKey($publicKey); // $pubKey is Crypt_RSA object
      $subject->setDN([
         'id-at-countryName' => 'FR',
         'id-at-stateOrProvinceName' => 'Seine-et-Marne',
         'id-at-localityName' => 'Melun',
         'id-at-organizationName' => 'Elcimai Informatique',
         'id-at-commonName' => '*.webank.fr',
      ]);
      $subject->setKeyIdentifier($subject->computeKeyIdentifier($publicKey)); // id-ce-subjectKeyIdentifier

      $issuer = new X509();
      $issuer->setPrivateKey($privateKey); // $privKey is Crypt_RSA object
      $issuer->setDN([
         'id-at-countryName' => 'US',
         'id-at-organizationName' => 'GeoTrust Inc.',
         'id-at-commonName' => 'GeoTrust SSL CA - G3',
      ]);
      $issuer->setKeyIdentifier($subject->computeKeyIdentifier($publicKey)); // id-ce-authorityKeyIdentifier

      $today = DateTime::createFromFormat('U', time());
      $x509 = new X509();

      $x509->startDate = $today->modify('-1 day')->format('YmdHis');
      $x509->endDate = $today->modify('+1 year')->format('YmdHis');
      $x509->serialNumber = self::generateSerialNumber();
      $result = $x509->sign($issuer, $subject, 'sha256WithRSAEncryption');
      $x509->loadX509($result);
      $x509->setExtension('id-ce-subjectAltName', [
         [
            'dNSName' => '*.webank.fr',
         ],
         [
            'dNSName' => 'webank.fr',
         ],
      ]);
      $x509->setExtension('id-ce-basicConstraints', [
         'cA' => false,
      ]);
      $x509->setExtension('id-ce-keyUsage', ['keyEncipherment', 'digitalSignature'], true);
      $x509->setExtension('id-ce-cRLDistributionPoints', [
         [
            'distributionPoint' =>
               [
                  'fullName' =>
                     [
                        [
                           'uniformResourceIdentifier' => 'http://gn.symcb.com/gn.crl',
                        ],
                     ],
               ],
         ]]);
      $x509->setExtension('id-ce-certificatePolicies', [
         [
            'policyIdentifier' => '2.23.140.1.2.2',
            'policyQualifiers' =>
               [
                  [
                     'policyQualifierId' => 'id-qt-cps',
                     'qualifier' =>
                        [
                           'ia5String' => 'https://www.geotrust.com/resources/repository/legal',
                        ],
                  ],
                  [
                     'policyQualifierId' => 'id-qt-unotice',
                     'qualifier' =>
                        [
                           'explicitText' =>
                              [
                                 'utf8String' => 'https://www.geotrust.com/resources/repository/legal',
                              ],
                        ],
                  ],
               ],
         ],
      ]);
      $x509->setExtension('id-ce-extKeyUsage', ['id-kp-serverAuth', 'id-kp-clientAuth']);
      $x509->setExtension('id-pe-authorityInfoAccess', [
         [
            'accessMethod' => 'id-ad-ocsp',
            'accessLocation' =>
               [
                  'uniformResourceIdentifier' => 'http://gn.symcd.com',
               ],
         ],
         [
            'accessMethod' => 'id-ad-caIssuers',
            'accessLocation' =>
               [
                  'uniformResourceIdentifier' => 'http://gn.symcb.com/gn.crt',
               ],
         ],
      ]);
      $x509->setExtension('1.3.6.1.4.1.11129.2.4.2',
         'BIIBbAFqAHcA3esdK3oNT6Ygi4GtgWhwfi6OnQHVXIiNPRHEzbbsvswAAAFdCJcynQAABAMASDBGAiEAgJgQE9466xkMy6olq+1xvTGt9ROXcgmdUIht4EE4g14CIQDZNjYcKbVU6taN/unn2WHlsDgphMgQXzALHt7vrI/bIgB2AKS5CZC0GFgUh7sTosxncAo8NZgE+RvfuON3zQ7IDdwQAAABXQiXMtAAAAQDAEcwRQIgTx+2uvI9ReTYiO9Ii85qoet1dc+y58RT4wAO9C4OCakCIQCRhO2kJWxeSfP1L2/Q24I3MGLMn//mwhdJ43mu4e9n8gB3AO5Lvbd1zmC64UJpH6vhnmajD35fsHLYgwDEe4l6qP3LAAABXQiXNJcAAAQDAEgwRgIhAM+dK3OLBL5nGzp/PSt3yRab85AD3jz69g5TqGdrMuhkAiEAnDMu/ZiqyBWO3+li3L9/hi3BcHX74rAmA3OX1jNxIKE='
      );
      $result = $x509->sign($issuer, $x509, 'sha256WithRSAEncryption');
      $certificateContent = $x509->saveX509($result);
      return $certificateContent;
   }

   /**
    * Generate 74 digits serial number represented in the string.
    * @return string
    */
   private static function generateSerialNumber(): string
   {
      // prevent the first number from being 0
      $result = rand(1, 9);
      for ($i = 0; $i < 74; $i++)
      {
         $result .= rand(0, 9);
      }
      return $result;
   }

   public static function buildCertificateEFromDetails(string $exponent, string $modulus, string $content = null): Certificate
   {
      return self::buildCertificateFromDetails(Certificate::TYPE_E, $exponent, $modulus, $content);
   }

   public static function buildCertificateXFromDetails(string $exponent, string $modulus, string $content = null): Certificate
   {
      return self::buildCertificateFromDetails(Certificate::TYPE_X, $exponent, $modulus, $content);
   }

   private static function buildCertificateFromDetails(string $type, string $exponent, string $modulus, string $content = null): Certificate
   {
      $rsa = new RSA();
      $rsa->loadKey([
         'n' => new BigInteger($modulus, 256),
         'e' => new BigInteger($exponent, 256),
      ]);
      $publicKey = $rsa->getPublicKey(RSA::PUBLIC_FORMAT_PKCS1);
      $privateKey = $rsa->getPrivateKey(RSA::PUBLIC_FORMAT_PKCS1);
      $certificate = new Certificate($type, $publicKey, $privateKey, $content);
      return $certificate;
   }
}
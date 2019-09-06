<?php

namespace AndrewSvirin\Ebics\factories;

use AndrewSvirin\Ebics\models\Certificate;
use DateTime;
use phpseclib\Crypt\RSA;
use phpseclib\File\X509;

/**
 * Class CertificateFactory represents producers for the @see Certificate.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class CertificateFactory
{

   public static function buildCertificateA(string $content, array $keys): Certificate
   {
      return new Certificate($content, Certificate::TYPE_A, $keys);
   }

   public static function buildCertificateE(string $content, array $keys): Certificate
   {
      return new Certificate($content, Certificate::TYPE_E, $keys);
   }

   public static function buildCertificateX(string $content, array $keys): Certificate
   {
      return new Certificate($content, Certificate::TYPE_X, $keys);
   }

   public static function generateCertificateA(string $password): Certificate
   {
      return self::generateCertificate($password, Certificate::TYPE_A);
   }

   public static function generateCertificateE(string $password): Certificate
   {
      return self::generateCertificate($password, Certificate::TYPE_E);
   }

   public static function generateCertificateX(string $password): Certificate
   {
      return self::generateCertificate($password, Certificate::TYPE_X);
   }

   private static function generateCertificate(string $password, string $type): Certificate
   {
      $keys = self::generateKeys($password);

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
      $x509->setExtension('id-ce-subjectAltName', array(
         array(
            'dNSName' => '*.webank.fr',
         ),
         array(
            'dNSName' => 'webank.fr',
         ),
      ));
      $x509->setExtension('id-ce-basicConstraints', array(
         'cA' => false,
      ));
      $x509->setExtension('id-ce-keyUsage', ['keyEncipherment', 'digitalSignature'], true);
      $x509->setExtension('id-ce-cRLDistributionPoints', array(
         array(
            'distributionPoint' =>
               array(
                  'fullName' =>
                     array(
                        array(
                           'uniformResourceIdentifier' => 'http://gn.symcb.com/gn.crl',
                        ),
                     ),
               ),
         )));
      $x509->setExtension('id-ce-certificatePolicies', array(
         array(
            'policyIdentifier' => '2.23.140.1.2.2',
            'policyQualifiers' =>
               array(
                  array(
                     'policyQualifierId' => 'id-qt-cps',
                     'qualifier' =>
                        array(
                           'ia5String' => 'https://www.geotrust.com/resources/repository/legal',
                        ),
                  ),
                  array(
                     'policyQualifierId' => 'id-qt-unotice',
                     'qualifier' =>
                        array(
                           'explicitText' =>
                              array(
                                 'utf8String' => 'https://www.geotrust.com/resources/repository/legal',
                              ),
                        ),
                  ),
               ),
         ),
      ));
      $x509->setExtension('id-ce-extKeyUsage', array('id-kp-serverAuth', 'id-kp-clientAuth'));
      $x509->setExtension('id-pe-authorityInfoAccess', array(
         array(
            'accessMethod' => 'id-ad-ocsp',
            'accessLocation' =>
               array(
                  'uniformResourceIdentifier' => 'http://gn.symcd.com',
               ),
         ),
         array(
            'accessMethod' => 'id-ad-caIssuers',
            'accessLocation' =>
               array(
                  'uniformResourceIdentifier' => 'http://gn.symcb.com/gn.crt',
               ),
         ),
      ));
      $x509->setExtension('1.3.6.1.4.1.11129.2.4.2',
         'BIIBbAFqAHcA3esdK3oNT6Ygi4GtgWhwfi6OnQHVXIiNPRHEzbbsvswAAAFdCJcynQAABAMASDBGAiEAgJgQE9466xkMy6olq+1xvTGt9ROXcgmdUIht4EE4g14CIQDZNjYcKbVU6taN/unn2WHlsDgphMgQXzALHt7vrI/bIgB2AKS5CZC0GFgUh7sTosxncAo8NZgE+RvfuON3zQ7IDdwQAAABXQiXMtAAAAQDAEcwRQIgTx+2uvI9ReTYiO9Ii85qoet1dc+y58RT4wAO9C4OCakCIQCRhO2kJWxeSfP1L2/Q24I3MGLMn//mwhdJ43mu4e9n8gB3AO5Lvbd1zmC64UJpH6vhnmajD35fsHLYgwDEe4l6qP3LAAABXQiXNJcAAAQDAEgwRgIhAM+dK3OLBL5nGzp/PSt3yRab85AD3jz69g5TqGdrMuhkAiEAnDMu/ZiqyBWO3+li3L9/hi3BcHX74rAmA3OX1jNxIKE='
      );
      $result = $x509->sign($issuer, $x509, 'sha256WithRSAEncryption');
      $certificateContent = $x509->saveX509($result);
      $certificate = new Certificate($certificateContent, $type, $keys);
      return $certificate;
   }

   /**
    * Generate public and private keys.
    * @param string $password
    * @return array [
    *    'publickey' => '<string>',
    *    'privatekey' => '<string>',
    * ]
    */
   private static function generateKeys(string $password)
   {
      $rsa = new RSA();
      $rsa->setPublicKeyFormat(RSA::PRIVATE_FORMAT_PKCS1);
      $rsa->setPrivateKeyFormat(RSA::PUBLIC_FORMAT_PKCS1);
      $rsa->setHash('sha256');
      $rsa->setMGFHash('sha256');
      $rsa->setPassword($password);
      $keys = $rsa->createKey(2048);
      return $keys;
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
}
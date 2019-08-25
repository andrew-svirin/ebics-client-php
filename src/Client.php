<?php

namespace AndrewSvirin\Ebics;

use AndrewSvirin\Ebics\handlers\BodyHandler;
use AndrewSvirin\Ebics\handlers\HeaderHandler;
use AndrewSvirin\Ebics\handlers\OrderDataHandler;
use AndrewSvirin\Ebics\handlers\RequestHandler;
use AndrewSvirin\Ebics\models\OrderData;
use AndrewSvirin\Ebics\models\Request;
use AndrewSvirin\Ebics\models\Response;
use DateTime;
use DOMDocument;
use phpseclib\Crypt\RSA;
use phpseclib\File\ASN1;
use phpseclib\File\X509;
use phpseclib\Math\BigInteger;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * EBICS client representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class Client
{

   /**
    * An EbicsBank instance.
    * @var Bank
    */
   private $bank;

   /**
    * An EbicsUser instance.
    * @var User
    */
   private $user;

   /**
    * @var RequestHandler
    */
   private $requestHandler;

   /**
    * @var HeaderHandler
    */
   private $headerHandler;

   /**
    * @var BodyHandler
    */
   private $bodyHandler;

   /**
    * @var KeyRing
    */
   private $keyRing;

   /**
    * @var OrderDataHandler
    */
   private $orderDataHandler;

   /**
    * Constructor.
    * @param Bank $bank
    * @param User $user
    * @param KeyRing $keyRing
    */
   public function __construct(Bank $bank, User $user, KeyRing $keyRing)
   {
      $this->bank = $bank;
      $this->user = $user;
      $this->keyRing = $keyRing;
      $this->requestHandler = new RequestHandler();
      $this->headerHandler = new HeaderHandler($bank, $user);
      $this->bodyHandler = new BodyHandler();
      $this->orderDataHandler = new OrderDataHandler($user, $keyRing);
   }

   /**
    * Getter for bank.
    * @return Bank
    */
   public function getBank()
   {
      return $this->bank;
   }

   /**
    * Getter for user.
    * @return User
    */
   public function getUser()
   {
      return $this->user;
   }

   /**
    * @param string $body
    * @return ResponseInterface
    * @throws TransportExceptionInterface
    */
   private function post($body): ResponseInterface
   {
      $httpClient = HttpClient::create();
      $response = $httpClient->request('POST', $this->bank->getUrl(), [
         'headers' => [
            'Content-Type' => 'text/xml; charset=ISO-8859-1',
         ],
         'body' => $body,
         'verify_peer' => false,
         'verify_host' => false,
      ]);
      return $response;
   }

   private function generateCertificate(RSA $publicKey, RSA $privateKey)
   {

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

      $x509_2 = new X509();
      $x509_2->loadX509($this->keyRing->getCertificateContent());

      $today = new DateTime();
      $x509 = new X509();

      $x509->startDate = $today->modify('-1 day')->format('YmdHis');
      $x509->endDate = $today->modify('+1 year')->format('YmdHis');
      $x509->serialNumber = $this->generateSerialNumber();
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
      return $certificateContent;
   }

   /**
    * Make INI request.
    * @return Response
    * @throws ClientExceptionInterface
    * @throws RedirectionExceptionInterface
    * @throws ServerExceptionInterface
    * @throws TransportExceptionInterface
    */
   public function INI(): Response
   {
      $keys = $this->keyRing->generateKeys();

      $privateKey = new RSA();
      $privateKey->loadKey($keys['privatekey']);

      $publicKey = new RSA();
      $publicKey->loadKey($keys['publickey']);
      $publicKey->setPublicKey();

      $certificateContent = $this->generateCertificate($publicKey, $privateKey);

      // Order data.
      $orderData = new OrderData();
      $this->orderDataHandler->handle($orderData, $certificateContent);
      $orderDataContent = $orderData->getContent();
      // Wrapper for request Order data.
      $request = new Request();
      $xmlRequest = $this->requestHandler->handleUnsecured($request);
      $this->headerHandler->handleINI($request, $xmlRequest);
      $this->bodyHandler->handle($request, $xmlRequest, $orderDataContent);
      $requestContent = $request->getContent();
      $hostResponse = $this->post($requestContent);
      $hostResponseContent = $hostResponse->getContent();
      $response = new Response();
      $response->loadXML($hostResponseContent);
      return $response;
   }

   /**
    * Make HIA request.
    * @param $data
    * @return string
    * @throws ClientExceptionInterface
    * @throws RedirectionExceptionInterface
    * @throws ServerExceptionInterface
    * @throws TransportExceptionInterface
    */
   public function HIA($data)
   {
      $keys1 = $this->keyRing->generateKeys();

      $privateKey1 = new RSA();
      $privateKey1->loadKey($keys1['privatekey']);

      $publicKey1 = new RSA();
      $publicKey1->loadKey($keys1['publickey']);
      $publicKey1->setPublicKey();

      $certificateContent1 = $this->generateCertificate($publicKey1, $privateKey1);

      $x5091 = new X509();
      $x5091->loadX509($certificateContent1);
      /* @var $serialNumber1 BigInteger */
      $serialNumber1 = $x5091->currentCert["tbsCertificate"]["serialNumber"];
      $serialNumberValue1 = $serialNumber1->toString();
      $insurerName1 = $x5091->getIssuerDNProp('id-at-commonName');
      $insurerNameValue1 = array_shift($insurerName1);

      // -------------
      $keys2 = $this->keyRing->generateKeys();

      $privateKey2 = new RSA();
      $privateKey2->loadKey($keys2['privatekey']);

      $publicKey2 = new RSA();
      $publicKey2->loadKey($keys2['publickey']);
      $publicKey2->setPublicKey();

      $certificateContent2 = $this->generateCertificate($publicKey2, $privateKey2);

      $x5092 = new X509();
      $x5092->loadX509($certificateContent2);
      /* @var $serialNumber2 BigInteger */
      $serialNumber2 = $x5092->currentCert["tbsCertificate"]["serialNumber"];
      $serialNumberValue2 = $serialNumber2->toString();
      $insurerName2 = $x5092->getIssuerDNProp('id-at-commonName');
      $insurerNameValue2 = array_shift($insurerName2);

      // -------------
//      $e001 = $rsa->sign($E001Digest);
//      $x001 = $rsa->sign($X001Digest);
      $exponent1 = $publicKey1->exponent->toHex();
      $modulus1 = $publicKey1->modulus->toHex();
      $exponent2 = $publicKey2->exponent->toHex();
      $modulus2 = $publicKey2->modulus->toHex();

//      $certificateContent = $this->keyRing->getCertificateContent();
//      $certificateData = $this->keyRing->getCertificateData();

      $data = str_replace([
         '{X002_Modulus}',
         '{X002_Exponent}',
         '{E002_Modulus}',
         '{E002_Exponent}',
         '{XX509IssuerName}',
         '{XX509SerialNumber}',
         '{XX509Certificate}',
         '{EX509IssuerName}',
         '{EX509SerialNumber}',
         '{EX509Certificate}',
         '{PartnerID}',
         '{UserID}',
      ], [
         base64_encode($modulus1),
         base64_encode($exponent1),
         base64_encode($modulus2),
         base64_encode($exponent2),
         $insurerNameValue1,
         $serialNumberValue1,
         base64_encode($certificateContent1),
         $insurerNameValue2,
         $serialNumberValue2,
         base64_encode($certificateContent2),
         $this->user->getPartnerId(),
         $this->user->getUserId(),

      ], $data);
      $xml = '<?xml version="1.0"?>
        <ebicsUnsecuredRequest xmlns="urn:org:ebics:H004" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" Revision="1" Version="H004">
          <header authenticate="true">
            <static>
              <HostID>' . $this->bank->getHostId() . '</HostID>
              <PartnerID>' . $this->user->getPartnerId() . '</PartnerID>
              <UserID>' . $this->user->getUserId() . '</UserID>
              <OrderDetails>
                <OrderType>HIA</OrderType>
                <OrderAttribute>DZNNN</OrderAttribute>
              </OrderDetails>
              <SecurityMedium>0000</SecurityMedium>
            </static>
            <mutable/>
          </header>
          <body>
            <DataTransfer>
              <OrderData>' . base64_encode(gzcompress($data)) . '</OrderData>
            </DataTransfer>
          </body>
        </ebicsUnsecuredRequest>';
      $hostResponse = $this->post($xml);
      $hostResponseContent = $hostResponse->getContent();
      $response = new Response();
      $response->loadXML($hostResponseContent);
      return $response;
   }

   private function getSubjectKeyIdentifier()
   {

   }

   private function getAuthorityKeyIdentifier()
   {
   }

   private function generateSerialNumber()
   {
      // prevent the first number from being 0
      $result = rand(1, 9);
      for ($i = 0; $i < 74; $i++)
      {
         $result .= rand(0, 9);
      }
      return $result;
   }

   /**
    * Downloads the bank account statement in SWIFT format (MT940).
    * @param int $start The start date of requested transactions.
    * @param int $end The end date of requested transactions.
    * @param boolean $parsed Flag whether the received MT940 message should be
    * parsed and returned as a dictionary or not.
    * @return
    */
   public function STA($start = NULL, $end = NULL, $parsed = FALSE)
   {
      return '';
   }

   /**
    * Downloads the interim transaction report in SWIFT format (MT942).
    * @param int $start The start date of requested transactions.
    * @param int $end The end date of requested transactions.
    * @param boolean $parsed Flag whether the received MT940 message should be
    * parsed and returned as a dictionary or not.
    * @return Response
    * @throws \Comodojo\Exception\HttpException
    * @throws exceptions\EbicsException
    * @throws \Exception
    */
   public function VMK($start = NULL, $end = NULL, $parsed = FALSE)
   {
      $domTree = new DOMDocument();

      // Add OrderDetails.
      $xmlOrderDetails = $domTree->createElement('OrderDetails');
      $domTree->appendChild($xmlOrderDetails);

      // Add OrderType.
      $xmlOrderType = $domTree->createElement('OrderType');
      $xmlOrderType->nodeValue = 'VMK';
      $xmlOrderDetails->appendChild($xmlOrderType);

      // Add OrderAttribute.
      $xmlOrderAttribute = $domTree->createElement('OrderAttribute');
      $xmlOrderAttribute->nodeValue = 'DZHNN';
      $xmlOrderDetails->appendChild($xmlOrderAttribute);

      // Add StandardOrderParams.
      $xmlStandardOrderParams = $domTree->createElement('StandardOrderParams');
      $xmlOrderDetails->appendChild($xmlStandardOrderParams);

      if ($start != NULL && $end != NULL)
      {
         // Add DateRange.
         $xmlDateRange = $domTree->createElement('DateRange');
         $xmlStandardOrderParams->appendChild($xmlDateRange);

         // Add Start.
         $xmlStart = $domTree->createElement('Start');
         $xmlStart->nodeValue = $start;
         $xmlDateRange->appendChild($xmlStart);
         // Add End.
         $xmlEnd = $domTree->createElement('End');
         $xmlEnd->nodeValue = $end;
         $xmlDateRange->appendChild($xmlEnd);
      }

      $request = new Request($this);
      $orderDetails = $domTree->getElementsByTagName('OrderDetails')->item(0);

      return $request->createRequest($orderDetails)->download();
   }

}

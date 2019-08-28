<?php

namespace AndrewSvirin\Ebics\handlers;

use AndrewSvirin\Ebics\KeyRing;
use AndrewSvirin\Ebics\models\Certificate;
use AndrewSvirin\Ebics\User;
use DateTime;
use DOMDocument;
use phpseclib\Math\BigInteger;

/**
 * Class OrderDataHandler manages OrderData DOM elements.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class OrderDataHandler
{

   /**
    * @var User
    */
   private $user;

   /**
    * @var KeyRing
    */
   private $keyRing;

   /**
    * @var string
    */
   private $signatureVersion;

   public function __construct(User $user, KeyRing $keyRing)
   {
      $this->user = $user;
      $this->keyRing = $keyRing;
      $this->signatureVersion = 'A006';
   }

   /**
    * Adds OrderData DOM elements to XML DOM.
    * @param DOMDocument $xml
    * @param Certificate $certificateA
    * @param DateTime|null $dateTime
    */
   public function handle(DOMDocument $xml, Certificate $certificateA, DateTime $dateTime = null)
   {
      $x509 = $certificateA->toX509();
      if (null === $dateTime)
      {
         $dateTime = DateTime::createFromFormat('U', time());
      }
      $exponent = $x509->getPublicKey()->exponent->toHex();
      $modulus = $x509->getPublicKey()->modulus->toHex();
      /* @var $serialNumber BigInteger */
      $serialNumber = $x509->currentCert["tbsCertificate"]["serialNumber"];
      $serialNumberValue = $serialNumber->toString();
      $insurerName = $x509->getIssuerDNProp('id-at-commonName');
      $insurerNameValue = array_shift($insurerName);
      $timeStamp = $dateTime->format('c');

      // Add SignaturePubKeyOrderData to root.
      $xmlSignaturePubKeyOrderData = $xml->createElementNS('http://www.ebics.org/S001', 'SignaturePubKeyOrderData');
      $xmlSignaturePubKeyOrderData->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ds', 'http://www.w3.org/2000/09/xmldsig#');
      $xml->appendChild($xmlSignaturePubKeyOrderData);

      // Add SignaturePubKeyInfo to SignaturePubKeyOrderData.
      $xmlSignaturePubKeyInfo = $xml->createElement('SignaturePubKeyInfo');
      $xmlSignaturePubKeyOrderData->appendChild($xmlSignaturePubKeyInfo);

      // Add ds:X509Data to SignaturePubKeyInfo.
      $xmlX509Data = $xml->createElement('ds:X509Data');
      $xmlSignaturePubKeyInfo->appendChild($xmlX509Data);

      // Add ds:X509IssuerSerial to ds:X509Data.
      $xmlX509IssuerSerial = $xml->createElement('ds:X509IssuerSerial');
      $xmlX509Data->appendChild($xmlX509IssuerSerial);

      // Add ds:X509IssuerName to ds:X509IssuerSerial.
      $xmlX509IssuerName = $xml->createElement('ds:X509IssuerName');
      $xmlX509IssuerName->nodeValue = $insurerNameValue;
      $xmlX509IssuerSerial->appendChild($xmlX509IssuerName);

      // Add ds:X509SerialNumber to ds:X509IssuerSerial.
      $xmlX509SerialNumber = $xml->createElement('ds:X509SerialNumber');
      $xmlX509SerialNumber->nodeValue = $serialNumberValue;
      $xmlX509IssuerSerial->appendChild($xmlX509SerialNumber);

      // Add ds:X509Certificate to ds:X509Data.
      $xmlX509Certificate = $xml->createElement('ds:X509Certificate');
      $xmlX509Certificate->nodeValue = base64_encode($certificateA->getContent());
      $xmlX509Data->appendChild($xmlX509Certificate);

      // Add PubKeyValue to SignaturePubKeyInfo.
      $xmlPubKeyValue = $xml->createElement('PubKeyValue');
      $xmlSignaturePubKeyInfo->appendChild($xmlPubKeyValue);

      // Add ds:RSAKeyValue to PubKeyValue.
      $xmlRSAKeyValue = $xml->createElement('ds:RSAKeyValue');
      $xmlPubKeyValue->appendChild($xmlRSAKeyValue);

      // Add ds:Modulus to ds:RSAKeyValue.
      $xmlModulus = $xml->createElement('ds:Modulus');
      $xmlModulus->nodeValue = base64_encode($modulus);
      $xmlRSAKeyValue->appendChild($xmlModulus);

      // Add ds:Exponent to ds:RSAKeyValue.
      $xmlExponent = $xml->createElement('ds:Exponent');
      $xmlExponent->nodeValue = base64_encode($exponent);
      $xmlRSAKeyValue->appendChild($xmlExponent);

      // Add TimeStamp to PubKeyValue.
      $xmlTimeStamp = $xml->createElement('TimeStamp');
      $xmlTimeStamp->nodeValue = $timeStamp;
      $xmlPubKeyValue->appendChild($xmlTimeStamp);

      // Add SignatureVersion to SignaturePubKeyInfo.
      $xmlSignatureVersion = $xml->createElement('SignatureVersion');
      $xmlSignatureVersion->nodeValue = $this->signatureVersion;
      $xmlSignaturePubKeyInfo->appendChild($xmlSignatureVersion);

      // Add PartnerID to SignaturePubKeyOrderData.
      $xmlPartnerID = $xml->createElement('PartnerID');
      $xmlPartnerID->nodeValue = $this->user->getPartnerId();
      $xmlSignaturePubKeyOrderData->appendChild($xmlPartnerID);

      // Add UserID to SignaturePubKeyOrderData.
      $xmlUserID = $xml->createElement('UserID');
      $xmlUserID->nodeValue = $this->user->getUserId();
      $xmlSignaturePubKeyOrderData->appendChild($xmlUserID);
   }

}
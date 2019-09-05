<?php

namespace AndrewSvirin\Ebics\handlers;

use AndrewSvirin\Ebics\models\Certificate;
use AndrewSvirin\Ebics\models\User;
use DateTime;
use DOMDocument;
use DOMNode;

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
    * @var string
    */
   private $signatureVersion;

   /**
    * @var string
    */
   private $authenticationVersion;

   /**
    * @var string
    */
   private $encryptionVersion;

   public function __construct(User $user)
   {
      $this->user = $user;
      $this->signatureVersion = 'A006';
      $this->authenticationVersion = 'X002';
      $this->encryptionVersion = 'E002';
   }

   /**
    * Adds OrderData DOM elements to XML DOM for INI request.
    * @param DOMDocument $xml
    * @param Certificate $certificateA Certificate A.
    * @param DateTime|null $dateTime
    */
   public function handleINI(DOMDocument $xml, Certificate $certificateA, DateTime $dateTime)
   {
      // Add SignaturePubKeyOrderData to root.
      $xmlSignaturePubKeyOrderData = $xml->createElementNS('http://www.ebics.org/S001', 'SignaturePubKeyOrderData');
      $xmlSignaturePubKeyOrderData->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ds', 'http://www.w3.org/2000/09/xmldsig#');
      $xml->appendChild($xmlSignaturePubKeyOrderData);

      // Add SignaturePubKeyInfo to SignaturePubKeyOrderData.
      $xmlSignaturePubKeyInfo = $xml->createElement('SignaturePubKeyInfo');
      $xmlSignaturePubKeyOrderData->appendChild($xmlSignaturePubKeyInfo);

      $this->handleX509Data($xmlSignaturePubKeyInfo, $xml, $certificateA);
      $this->handlePubKeyValue($xmlSignaturePubKeyInfo, $xml, $certificateA, $dateTime);

      // Add SignatureVersion to SignaturePubKeyInfo.
      $xmlSignatureVersion = $xml->createElement('SignatureVersion');
      $xmlSignatureVersion->nodeValue = $this->signatureVersion;
      $xmlSignaturePubKeyInfo->appendChild($xmlSignatureVersion);

      // Add PartnerID to SignaturePubKeyOrderData.
      $this->handlePartnerId($xmlSignaturePubKeyOrderData, $xml);

      // Add UserID to SignaturePubKeyOrderData.
      $this->handleUserId($xmlSignaturePubKeyOrderData, $xml);
   }

   /**
    * Adds OrderData DOM elements to XML DOM for HIA request.
    * @param DOMDocument $xml
    * @param Certificate $certificateE
    * @param Certificate $certificateX
    * @param DateTime|null $dateTime
    */
   public function handleHIA(DOMDocument $xml, Certificate $certificateE, Certificate $certificateX, DateTime $dateTime)
   {
      // Add HIARequestOrderData to root.
      $xmlHIARequestOrderData = $xml->createElementNS('urn:org:ebics:H004', 'HIARequestOrderData');
      $xmlHIARequestOrderData->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ds', 'http://www.w3.org/2000/09/xmldsig#');
      $xml->appendChild($xmlHIARequestOrderData);

      // Add AuthenticationPubKeyInfo to HIARequestOrderData.
      $xmlAuthenticationPubKeyInfo = $xml->createElement('AuthenticationPubKeyInfo');
      $xmlHIARequestOrderData->appendChild($xmlAuthenticationPubKeyInfo);

      $this->handleX509Data($xmlAuthenticationPubKeyInfo, $xml, $certificateX);
      $this->handlePubKeyValue($xmlAuthenticationPubKeyInfo, $xml, $certificateX, $dateTime);

      // Add AuthenticationVersion to AuthenticationPubKeyInfo.
      $xmlAuthenticationVersion = $xml->createElement('AuthenticationVersion');
      $xmlAuthenticationVersion->nodeValue = $this->authenticationVersion;
      $xmlAuthenticationPubKeyInfo->appendChild($xmlAuthenticationVersion);

      // Add EncryptionPubKeyInfo to HIARequestOrderData.
      $xmlEncryptionPubKeyInfo = $xml->createElement('EncryptionPubKeyInfo');
      $xmlHIARequestOrderData->appendChild($xmlEncryptionPubKeyInfo);

      $this->handleX509Data($xmlEncryptionPubKeyInfo, $xml, $certificateE);
      $this->handlePubKeyValue($xmlEncryptionPubKeyInfo, $xml, $certificateE, $dateTime);

      // Add EncryptionVersion to EncryptionPubKeyInfo.
      $xmlEncryptionVersion = $xml->createElement('EncryptionVersion');
      $xmlEncryptionVersion->nodeValue = $this->encryptionVersion;
      $xmlEncryptionPubKeyInfo->appendChild($xmlEncryptionVersion);

      // Add PartnerID to HIARequestOrderData.
      $this->handlePartnerId($xmlHIARequestOrderData, $xml);

      // Add UserID to HIARequestOrderData.
      $this->handleUserId($xmlHIARequestOrderData, $xml);
   }

   /**
    * Add ds:X509Data to PublicKeyInfo XML Node.
    * @param DOMNode $xmlPublicKeyInfo
    * @param DOMDocument $xml
    * @param Certificate $certificate
    */
   private function handleX509Data(DOMNode $xmlPublicKeyInfo, DOMDocument $xml, Certificate $certificate)
   {
      $certificateX509 = $certificate->toX509();
      // Add ds:X509Data to Signature.
      $xmlX509Data = $xml->createElement('ds:X509Data');
      $xmlPublicKeyInfo->appendChild($xmlX509Data);

      // Add ds:X509IssuerSerial to ds:X509Data.
      $xmlX509IssuerSerial = $xml->createElement('ds:X509IssuerSerial');
      $xmlX509Data->appendChild($xmlX509IssuerSerial);

      // Add ds:X509IssuerName to ds:X509IssuerSerial.
      $xmlX509IssuerName = $xml->createElement('ds:X509IssuerName');
      $xmlX509IssuerName->nodeValue = $certificateX509->getInsurerName();
      $xmlX509IssuerSerial->appendChild($xmlX509IssuerName);

      // Add ds:X509SerialNumber to ds:X509IssuerSerial.
      $xmlX509SerialNumber = $xml->createElement('ds:X509SerialNumber');
      $xmlX509SerialNumber->nodeValue = $certificateX509->getSerialNumber();
      $xmlX509IssuerSerial->appendChild($xmlX509SerialNumber);

      // Add ds:X509Certificate to ds:X509Data.
      $xmlX509Certificate = $xml->createElement('ds:X509Certificate');
      $xmlX509Certificate->nodeValue = base64_encode($certificate->getContent());
      $xmlX509Data->appendChild($xmlX509Certificate);
   }

   /**
    * Add PubKeyValue to PublicKeyInfo XML Node.
    * @param DOMNode $xmlPublicKeyInfo
    * @param DOMDocument $xml
    * @param Certificate $certificate
    * @param DateTime|null $dateTime
    */
   private function handlePubKeyValue(DOMNode $xmlPublicKeyInfo, DOMDocument $xml, Certificate $certificate, DateTime $dateTime = null)
   {
      $certificateX509 = $certificate->toX509();
      // Add PubKeyValue to Signature.
      $xmlPubKeyValue = $xml->createElement('PubKeyValue');
      $xmlPublicKeyInfo->appendChild($xmlPubKeyValue);

      // Add ds:RSAKeyValue to PubKeyValue.
      $xmlRSAKeyValue = $xml->createElement('ds:RSAKeyValue');
      $xmlPubKeyValue->appendChild($xmlRSAKeyValue);

      // Add ds:Modulus to ds:RSAKeyValue.
      $xmlModulus = $xml->createElement('ds:Modulus');
      $xmlModulus->nodeValue = base64_encode($certificateX509->getPublicKey()->exponent->toHex());
      $xmlRSAKeyValue->appendChild($xmlModulus);

      // Add ds:Exponent to ds:RSAKeyValue.
      $xmlExponent = $xml->createElement('ds:Exponent');
      $xmlExponent->nodeValue = base64_encode($certificateX509->getPublicKey()->exponent->toHex());
      $xmlRSAKeyValue->appendChild($xmlExponent);

      // Add TimeStamp to PubKeyValue.
      $xmlTimeStamp = $xml->createElement('TimeStamp');
      $xmlTimeStamp->nodeValue = $dateTime->format('c');
      $xmlPubKeyValue->appendChild($xmlTimeStamp);
   }

   /**
    * Add PartnerID to OrderData XML Node.
    * @param DOMNode $xmlOrderData
    * @param DOMDocument $xml
    */
   private function handlePartnerId(DOMNode $xmlOrderData, DOMDocument $xml)
   {
      $xmlPartnerID = $xml->createElement('PartnerID');
      $xmlPartnerID->nodeValue = $this->user->getPartnerId();
      $xmlOrderData->appendChild($xmlPartnerID);
   }

   /**
    * Add UserID to OrderData XML Node.
    * @param DOMNode $xmlOrderData
    * @param DOMDocument $xml
    */
   private function handleUserId(DOMNode $xmlOrderData, DOMDocument $xml)
   {
      $xmlUserID = $xml->createElement('UserID');
      $xmlUserID->nodeValue = $this->user->getUserId();
      $xmlOrderData->appendChild($xmlUserID);
   }

}
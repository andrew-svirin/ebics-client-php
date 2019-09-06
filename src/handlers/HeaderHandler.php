<?php

namespace AndrewSvirin\Ebics\handlers;

use AndrewSvirin\Ebics\models\Bank;
use AndrewSvirin\Ebics\models\User;
use DateTime;
use DOMDocument;
use DOMElement;
use phpseclib\Crypt\Random;

/**
 * Class HeaderHandler manages header DOM elements.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class HeaderHandler
{

   const ORDER_TYPE_INI = 'INI';
   const ORDER_TYPE_HIA = 'HIA';
   const ORDER_TYPE_VMK = 'VMK';
   const ORDER_TYPE_HPB = 'HPB';

   const ORDER_ATTRIBUTE_DZNNN = 'DZNNN';
   const ORDER_ATTRIBUTE_DZHNN = 'DZHNN';

   /**
    * @var User
    */
   private $user;

   /**
    * @var Bank
    */
   private $bank;

   /**
    * @var string
    */
   private $language;

   /**
    * @var string
    */
   private $securityMedium;

   /**
    * @var string
    */
   private $product;

   public function __construct(Bank $bank, User $user)
   {
      $this->user = $user;
      $this->bank = $bank;
      $this->language = 'de';
      $this->securityMedium = '0000';
      $this->product = 'Ebics client PHP';
   }

   /**
    * Add header for INI Request XML.
    * @param DOMDocument $xml
    * @param DOMElement $xmlRequest
    * @return DOMElement
    */
   public function handleINI(DOMDocument $xml, DOMElement $xmlRequest)
   {
      return $this->handle($xml, $xmlRequest, self::ORDER_TYPE_INI, self::ORDER_ATTRIBUTE_DZNNN);
   }

   /**
    * Add header for HIA Request XML.
    * @param DOMDocument $xml
    * @param DOMElement $xmlRequest
    * @return DOMElement
    */
   public function handleHIA(DOMDocument $xml, DOMElement $xmlRequest)
   {
      return $this->handle($xml, $xmlRequest, self::ORDER_TYPE_HIA, self::ORDER_ATTRIBUTE_DZNNN);
   }

   /**
    * Add header for HPB Request XML.
    * @param DOMDocument $xml
    * @param DOMElement $xmlRequest
    * @param DateTime $dateTime
    * @return DOMElement
    */
   public function handleHPB(DOMDocument $xml, DOMElement $xmlRequest, DateTime $dateTime)
   {
      return $this->handle($xml, $xmlRequest, self::ORDER_TYPE_HPB, self::ORDER_ATTRIBUTE_DZHNN, $dateTime);
   }

   /**
    * Add header and children elements to DOM XML.
    * @param DOMDocument $xml
    * @param DOMElement $xmlRequest
    * @param string $orderType
    * @param string $orderAttribute
    * @param DateTime|null $dateTime Stamped by date time and Nonce.
    * @return DOMElement
    */
   private function handle(DOMDocument $xml, DOMElement $xmlRequest, $orderType, $orderAttribute, DateTime $dateTime = null): DOMElement
   {
      // Add header to request.
      $xmlHeader = $xml->createElement('header');
      $xmlHeader->setAttribute('authenticate', 'true');
      $xmlRequest->appendChild($xmlHeader);

      // Add static to header.
      $xmlStatic = $xml->createElement('static');
      $xmlHeader->appendChild($xmlStatic);

      // Add HostID to static.
      $xmlHostId = $xml->createElement('HostID');
      $xmlHostId->nodeValue = $this->bank->getHostId();
      $xmlStatic->appendChild($xmlHostId);

      if ($dateTime)
      {
         // Add Nonce to static.
         $xmlNonce = $xml->createElement('Nonce');
         $xmlNonce->nodeValue = $this->calculateNonce();
         $xmlStatic->appendChild($xmlNonce);

         // Add TimeStamp to static.
         $xmlTimeStamp = $xml->createElement('Timestamp');
         $xmlTimeStamp->nodeValue = $dateTime->format('Y-m-d\TH:i:s\Z');
         $xmlStatic->appendChild($xmlTimeStamp);
      }

      // Add PartnerID to static.
      $xmlPartnerId = $xml->createElement('PartnerID');
      $xmlPartnerId->nodeValue = $this->user->getPartnerId();
      $xmlStatic->appendChild($xmlPartnerId);

      // Add UserID to static.
      $xmlUserId = $xml->createElement('UserID');
      $xmlUserId->nodeValue = $this->user->getUserId();
      $xmlStatic->appendChild($xmlUserId);

      // Add Product to static.
      $xmlProduct = $xml->createElement('Product');
      $xmlProduct->setAttribute('Language', $this->language);
      $xmlProduct->nodeValue = $this->product;
      $xmlStatic->appendChild($xmlProduct);

      // Add OrderDetails to static.
      $xmlOrderDetails = $xml->createElement('OrderDetails');
      $xmlStatic->appendChild($xmlOrderDetails);

      // Add OrderType to OrderDetails.
      $xmlOrderType = $xml->createElement('OrderType');
      $xmlOrderType->nodeValue = $orderType;
      $xmlOrderDetails->appendChild($xmlOrderType);

      if (false)
      {
         // Add BankPubKeyDigests.
         $xmlBankPubKeyDigests = $xml->createElement('BankPubKeyDigests');
         $xmlStatic->appendChild($xmlBankPubKeyDigests);

         // Add Authentication.
         $xmlAuthentication = $xml->createElement('Authentication');
         $xmlAuthentication->setAttribute('Version', 'X001');
         $xmlAuthentication->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmlenc#sha256');
         $xmlAuthentication->nodeValue = '9BF804AF2B121A5B94C82BFD8E406FFB18024D3D4BF9E';
         $xmlBankPubKeyDigests->appendChild($xmlAuthentication);

         // Add Encryption.
         $xmlEncryption = $xml->createElement('Encryption');
         $xmlEncryption->setAttribute('Version', 'E001');
         $xmlEncryption->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmlenc#sha256');
         $xmlEncryption->nodeValue = '9BF804AF2B121A5B94C82BFD8E406FFB18024D3D4BF9E';
         $xmlBankPubKeyDigests->appendChild($xmlEncryption);
      }

      // Add OrderAttribute to OrderDetails.
      $xmlOrderAttribute = $xml->createElement('OrderAttribute');
      $xmlOrderAttribute->nodeValue = $orderAttribute;
      $xmlOrderDetails->appendChild($xmlOrderAttribute);

      // Add SecurityMedium to static.
      $xmlSecurityMedium = $xml->createElement('SecurityMedium');
      $xmlSecurityMedium->nodeValue = $this->securityMedium;
      $xmlStatic->appendChild($xmlSecurityMedium);

      // Add mutable to header.
      $xmlMutable = $xml->createElement('mutable');
      $xmlHeader->appendChild($xmlMutable);

      return $xmlHeader;
   }

   /**
    * Calculate Nonce.
    * @return string HEX
    */
   private function calculateNonce()
   {
      $bytes = Random::string(16);
      $nonce = bin2hex($bytes);
      $nonceUpper = strtoupper($nonce);
      return $nonceUpper;
   }

}
<?php

namespace AndrewSvirin\Ebics\handlers;

use AndrewSvirin\Ebics\models\Bank;
use AndrewSvirin\Ebics\models\User;
use DOMDocument;
use DOMElement;

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
    * Add header for INI request.
    * @param DOMDocument $xml
    * @param DOMElement $xmlRequest
    */
   public function handleINI(DOMDocument $xml, DOMElement $xmlRequest)
   {
      $this->handle($xml, $xmlRequest, self::ORDER_TYPE_INI, self::ORDER_ATTRIBUTE_DZNNN);
   }

   /**
    * Add header for HIA request.
    * @param DOMDocument $xml
    * @param DOMElement $xmlRequest
    */
   public function handleHIA(DOMDocument $xml, DOMElement $xmlRequest)
   {
      $this->handle($xml, $xmlRequest, self::ORDER_TYPE_HIA, self::ORDER_ATTRIBUTE_DZNNN);
   }

   /**
    * Add header and children elements to DOM XML.
    * @param DOMDocument $xml
    * @param DOMElement $xmlRequest
    * @param string $orderType
    * @param string $orderAttribute
    */
   private function handle(DOMDocument $xml, DOMElement $xmlRequest, $orderType, $orderAttribute)
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
   }

}
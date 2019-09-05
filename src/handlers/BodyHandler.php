<?php

namespace AndrewSvirin\Ebics\handlers;

use DOMDocument;
use DOMElement;

/**
 * Class BodyHandler manage body DOM elements.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class BodyHandler
{

   /**
    * @var bool
    */
   private $compress;

   /**
    * @var bool
    */
   private $encode;

   public function __construct()
   {
      $this->compress = TRUE;
      $this->encode = TRUE;
   }

   /**
    * Add body and children elements to request.
    * @param DOMDocument $xml
    * @param DOMElement $xmlRequest
    * @param string $orderData
    */
   public function handle(DOMDocument $xml, DOMElement $xmlRequest, $orderData)
   {
      // Add body to request.
      $xmlBody = $xml->createElement('body');
      $xmlRequest->appendChild($xmlBody);

      // Add DataTransfer to body.
      $xmlDataTransfer = $xml->createElement('DataTransfer');
      $xmlBody->appendChild($xmlDataTransfer);

      // Add OrderData to DataTransfer.
      $xmlOrderData = $xml->createElement('OrderData');
      if ($this->compress)
      {
         $orderData = gzcompress($orderData);
      }
      if ($this->encode)
      {
         $orderData = base64_encode($orderData);
      }
      $xmlOrderData->nodeValue = $orderData;
      $xmlDataTransfer->appendChild($xmlOrderData);
   }

   /**
    * Add empty body element to request.
    * @param DOMDocument $xml
    * @param DOMElement $xmlRequest
    */
   public function handleEmpty(DOMDocument $xml, DOMElement $xmlRequest)
   {
      // Add body to request.
      $xmlBody = $xml->createElement('body');
      $xmlRequest->appendChild($xmlBody);
   }

}
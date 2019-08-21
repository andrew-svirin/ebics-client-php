<?php

namespace AndrewSvirin\Ebics\handlers;

use DOMDocument;
use DOMElement;

/**
 * Class RequestHandler manage request DOM elements.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class RequestHandler
{

   const EBICS_REQUEST = 'ebicsRequest';
   const EBICS_UNSECURED_REQUEST = 'ebicsUnsecuredRequest';

   /**
    * Add Secured Request to DOM XML.
    * @param DOMDocument $xml
    * @return DOMElement
    */
   public function handleSecured(DOMDocument $xml): DOMElement
   {
      $xmlRequest = $this->handle($xml, self::EBICS_REQUEST);
      return $xmlRequest;
   }

   /**
    * Add Unsecured Request to DOM XML.
    * @param DOMDocument $xml
    * @return DOMElement
    */
   public function handleUnsecured(DOMDocument $xml): DOMElement
   {
      $xmlRequest = $this->handle($xml, self::EBICS_UNSECURED_REQUEST);
      return $xmlRequest;
   }

   /**
    * Add Request to DOM XML.
    * @param DOMDocument $xml
    * @param string $request
    * @return DOMElement
    */
   private function handle(DOMDocument $xml, $request): DOMElement
   {
      $xmlRequest = $xml->createElementNS('urn:org:ebics:H004', $request);
      $xmlRequest->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ds', 'http://www.w3.org/2000/09/xmldsig#');
      $xmlRequest->setAttribute('Version', 'H004');
      $xmlRequest->setAttribute('Revision', '1');
      $xml->appendChild($xmlRequest);
      return $xmlRequest;
   }
}
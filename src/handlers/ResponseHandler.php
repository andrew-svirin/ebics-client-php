<?php

namespace AndrewSvirin\Ebics\handlers;

use DOMDocument;
use DOMXPath;

/**
 * Class ResponseHandler manage response DOM elements.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class ResponseHandler
{

   /**
    * Extract KeyManagementResponse > body > ReturnCode value from the DOM XML.
    * @param DOMDocument $xml
    * @return string
    */
   public function retrieveKeyManagementResponseReturnCode(DOMDocument $xml)
   {
      $xpath = new DomXpath($xml);
      $xpath->registerNamespace('H004', 'urn:org:ebics:H004');
      $returnCodeNode = $xpath->query('/H004:ebicsKeyManagementResponse/H004:body/H004:ReturnCode');
      $returnCode = $returnCodeNode->item(0)->nodeValue;
      return $returnCode;
   }

}
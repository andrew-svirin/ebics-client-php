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
    * Extract KeyManagementResponse > header > mutable > ReturnCode value from the DOM XML.
    * @param DOMDocument $xml
    * @return string
    */
   public function retrieveKeyManagementResponseReturnCode(DOMDocument $xml)
   {
      $xpath = new DomXpath($xml);
      $xpath->registerNamespace('H004', 'urn:org:ebics:H004');
      $returnCodeNode = $xpath->query('/H004:ebicsKeyManagementResponse/H004:header/H004:mutable/H004:ReturnCode');
      $returnCode = $returnCodeNode->item(0)->nodeValue;
      return $returnCode;
   }

   /**
    * Extract KeyManagementResponse > header > mutable > ReportText value from the DOM XML.
    * @param DOMDocument $xml
    * @return string
    */
   public function retrieveKeyManagementResponseReportText(DOMDocument $xml)
   {
      $xpath = new DomXpath($xml);
      $xpath->registerNamespace('H004', 'urn:org:ebics:H004');
      $returnReportText = $xpath->query('/H004:ebicsKeyManagementResponse/H004:header/H004:mutable/H004:ReportText');
      $returnReportText = $returnReportText->item(0)->nodeValue;
      return $returnReportText;
   }

}
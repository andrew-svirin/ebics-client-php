<?php

namespace AndrewSvirin\Ebics\Handlers\Traits;

use DOMDocument;
use DOMXPath;

/**
 * Class XPathTrait manage XPath building.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
trait XPathTrait
{

   /**
    * Setup H004 XPath for DOM XML.
    * @param DOMDocument $xml
    * @return DOMXPath
    */
   private function prepareH004XPath(DOMDocument $xml): DOMXPath
   {
      $xpath = new DomXpath($xml);
      $xpath->registerNamespace('H004', 'urn:org:ebics:H004');
      $xpath->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');
      return $xpath;
   }

   /**
    * Setup H000 XPath for DOM XML.
    * @param DOMDocument $xml
    * @return DOMXPath
    */
   private function prepareH000XPath(DOMDocument $xml): DOMXPath
   {
      $xpath = new DomXpath($xml);
      $xpath->registerNamespace('H000', 'http://www.ebics.org/H000');
      return $xpath;
   }

   /**
    * Setup S001 XPath for DOM XML.
    * @param DOMDocument $xml
    * @return DOMXPath
    */
   private function prepareS001XPath(DOMDocument $xml): DOMXPath
   {
      $xpath = new DomXpath($xml);
      $xpath->registerNamespace('S001', 'http://www.ebics.org/S001');
      return $xpath;
   }

}

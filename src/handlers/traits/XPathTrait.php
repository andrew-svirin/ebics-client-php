<?php

namespace AndrewSvirin\Ebics\handlers\traits;

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
    * Setup XPath for DOM XML.
    * @param DOMDocument $xml
    * @return DOMXPath
    */
   private function prepareHXPath(DOMDocument $xml): DOMXPath
   {
      $xpath = new DomXpath($xml);
      $xpath->registerNamespace('H004', 'urn:org:ebics:H004');
      return $xpath;
   }

}
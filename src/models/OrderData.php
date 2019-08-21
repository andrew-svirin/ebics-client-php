<?php

namespace AndrewSvirin\Ebics\models;

use DOMDocument;

/**
 * Class OrderData represents OrderData model.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class OrderData extends DOMDocument
{

   public function __construct()
   {
      parent::__construct('1.0', 'UTF-8');
   }

   /**
    * Get formatted content.
    * @return string
    */
   public function getContent()
   {
      $this->preserveWhiteSpace = false;
      $this->formatOutput = true;
      return $this->saveXML();
   }

}
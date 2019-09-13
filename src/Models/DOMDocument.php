<?php

namespace AndrewSvirin\Ebics\Models;

/**
 * Class DOMDocument customize \DOMDocument.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class DOMDocument extends \DOMDocument
{

   public function __construct()
   {
      parent::__construct('1.0', 'utf-8');
      $this->preserveWhiteSpace = false;
   }

   /**
    * Get formatted content.
    * @return string
    */
   public function getContent(): string
   {
      $content = $this->saveXML();
      $content = str_replace('<?xml version="1.0" encoding="utf-8"?>', "<?xml version='1.0' encoding='utf-8'?>", $content);
      $content = trim($content);
      return $content;
   }

   /**
    * Get formatted content.
    * @return string
    */
   public function getFormattedContent(): string
   {
      $this->formatOutput = true;
      return $this->saveXML();
   }

}
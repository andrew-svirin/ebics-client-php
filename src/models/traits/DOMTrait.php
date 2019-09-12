<?php

namespace AndrewSvirin\Ebics\models;

/**
 * DOMTrait representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @property bool $preserveWhiteSpace
 * @property bool $formatOutput
 * @method saveXML()
 */
trait DOMTrait
{

   private $encoding = 'utf-8';

   /**
    * Prepare DOM XML.
    */
   private function prepareDOM()
   {
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

   public function getFormattedContent(): string
   {
      $this->formatOutput = true;
      return $this->saveXML();
   }

}
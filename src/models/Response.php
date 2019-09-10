<?php

namespace AndrewSvirin\Ebics\models;

use DOMDocument;

/**
 * Response model represents Response model.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class Response extends DOMDocument
{

   /**
    * Uses for encrypted OrderData.
    * @var OrderData
    */
   private $orderData;

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

   /**
    * @return OrderData
    */
   public function getOrderData(): OrderData
   {
      return $this->orderData;
   }

   /**
    * @param OrderData $orderData
    */
   public function setOrderData(OrderData $orderData): void
   {
      $this->orderData = $orderData;
   }

}

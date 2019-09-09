<?php

namespace AndrewSvirin\Ebics\models;

/**
 * Class OrderDataEncrypted represents OrderData Response model.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class OrderDataEncrypted
{
   /**
    * @var string
    */
   private $orderData;

   /**
    * @var string Binary value.
    */
   private $transactionId;

   public function __construct(string $orderData, string $transactionId)
   {
      $this->orderData = $orderData;
      $this->transactionId = $transactionId;
   }

   /**
    * @return string
    */
   public function getOrderData(): string
   {
      return $this->orderData;
   }

   /**
    * @return string
    */
   public function getTransactionId(): string
   {
      return $this->transactionId;
   }

}
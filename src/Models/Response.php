<?php

namespace AndrewSvirin\Ebics\Models;

/**
 * Response model represents Response model.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class Response extends DOMDocument
{

   /**
    * @var Transaction[]
    */
   private $transactions = [];
   /**
    * @var OrderDataDecrypted
    */
   private $decryptedOrderData;

   /**
    * @param Transaction $transaction
    */
   public function addTransaction(Transaction $transaction)
   {
      $this->transactions[] = $transaction;
   }

   /**
    * @return Transaction[]
    */
   public function getTransactions(): array
   {
      return $this->transactions;
   }

   /**
    * @return OrderDataDecrypted
    */
   public function getDecryptedOrderData(): ?OrderDataDecrypted
   {
      return $this->decryptedOrderData;
   }

   /**
    * @param OrderDataDecrypted $decryptedOrderData
    * @return self
    */
   public function setDecryptedOrderData(OrderDataDecrypted $decryptedOrderData): self
   {
      $this->decryptedOrderData = $decryptedOrderData;

      return $this;
   }


}

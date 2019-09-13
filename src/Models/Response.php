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
   private $transactions;

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

}

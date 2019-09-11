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
    * @var Transaction[]
    */
   private $transactions;

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

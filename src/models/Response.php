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

   public function __construct()
   {
      parent::__construct('1.0', 'UTF-8');
      $this->preserveWhiteSpace = false;
   }

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

<?php

namespace AndrewSvirin\Ebics\Models;

use AndrewSvirin\Ebics\Contracts\TransactionInterface;

/**
 * Response model represents Transaction model.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class Transaction implements TransactionInterface
{

   /**
    * @var string
    */
   private $id;

   /**
    * @var string
    */
   private $phase;

   /**
    * @var int
    */
   private $segmentNumber;

   /**
    * @var int
    */
   private $numSegments;

   /**
    * Uses for encrypted OrderData.
    * @var OrderData
    */
   private $orderData;

   /**
    * @return string
    */
   public function getPhase(): string
   {
      return $this->phase;
   }

   /**
    * @param string $phase
    */
   public function setPhase(string $phase): void
   {
      $this->phase = $phase;
   }

   /**
    * @return string
    */
   public function getId(): string
   {
      return $this->id;
   }

   /**
    * @param string $id
    */
   public function setId(string $id): void
   {
      $this->id = $id;
   }

   /**
    * @return int
    */
   public function getSegmentNumber(): int
   {
      return $this->segmentNumber;
   }

   /**
    * @param int $segmentNumber
    */
   public function setSegmentNumber(int $segmentNumber): void
   {
      $this->segmentNumber = $segmentNumber;
   }

   /**
    * @return int
    */
   public function getNumSegments(): int
   {
      return $this->numSegments;
   }

   /**
    * @param int $numSegments
    */
   public function setNumSegments(int $numSegments): void
   {
      $this->numSegments = $numSegments;
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
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
     *
     * @var OrderData
     */
    private $orderData;

    /**
     * Uses for encrypted OrderData.
     *
     * @var string
     */
    private $plainOrderData;

    public function getPhase(): string
    {
        return $this->phase;
    }

    public function setPhase(string $phase): void
    {
        $this->phase = $phase;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getSegmentNumber(): int
    {
        return $this->segmentNumber;
    }

    public function setSegmentNumber(int $segmentNumber): void
    {
        $this->segmentNumber = $segmentNumber;
    }

    public function getNumSegments(): int
    {
        return $this->numSegments;
    }

    public function setNumSegments(int $numSegments): void
    {
        $this->numSegments = $numSegments;
    }

    public function getPlainOrderData(): string
    {
        return $this->plainOrderData;
    }

    public function setPlainOrderData(string $plainOrderData): void
    {
        $this->plainOrderData = $plainOrderData;
    }

    public function getOrderData(): OrderData
    {
        return $this->orderData;
    }

    public function setOrderData(OrderData $orderData): void
    {
        $this->orderData = $orderData;
    }
}

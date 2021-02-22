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
     * Uses for decoded OrderData Items.
     *
     * @var array
     */
    private $orderDataItems;

    /**
     * Uses for encrypted OrderData.
     *
     * @var string
     */
    private $plainOrderData;

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
     * @return string
     */
    public function getPlainOrderData(): string
    {
        return $this->plainOrderData;
    }

    /**
     * @param string $plainOrderData
     */
    public function setPlainOrderData(string $plainOrderData): void
    {
        $this->plainOrderData = $plainOrderData;
    }

    /**
     * @return OrderData
     */
    public function getOrderData(): OrderData
    {
        return $this->orderDataItems[0];
    }

    public function setOrderData(OrderData $orderData): void
    {
        $this->orderDataItems[0] = $orderData;
    }

    /**
     * @return OrderData[]
     */
    public function getOrderDataItems(): array
    {
        return $this->orderDataItems;
    }

    public function setOrderDataItems(array $orderDataItems): void
    {
        $this->orderDataItems = $orderDataItems;
    }
}

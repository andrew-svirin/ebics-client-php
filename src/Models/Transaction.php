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
     * @var int|null
     */
    private $segmentNumber;

    /**
     * @var int|null
     */
    private $numSegments;

    /**
     * @var string|null
     */
    private $orderId;

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

    public function getSegmentNumber(): ?int
    {
        return $this->segmentNumber;
    }

    public function setSegmentNumber(int $segmentNumber = null): void
    {
        $this->segmentNumber = $segmentNumber;
    }

    public function getNumSegments(): ?int
    {
        return $this->numSegments;
    }

    public function setNumSegments(int $numSegments = null): void
    {
        $this->numSegments = $numSegments;
    }

    public function setOrderId(string $orderId = null): void
    {
        $this->orderId = $orderId;
    }

    public function getOrderId(): ?string
    {
        return $this->orderId;
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

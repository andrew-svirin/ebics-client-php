<?php

namespace AndrewSvirin\Ebics\Models;

use AndrewSvirin\Ebics\Contracts\TransactionInterface;
use AndrewSvirin\Ebics\Exceptions\EbicsException;

/**
 * Response model represents Transaction model.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class Transaction implements TransactionInterface
{
    /**
     * @var string|null
     */
    private $id;

    /**
     * @var string|null
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
     * Uses for encrypted OrderData.
     *
     * @var OrderData|null
     */
    private $orderData;

    /**
     * Uses for encrypted OrderData.
     *
     * @var string|null
     */
    private $plainOrderData;

    public static function buildTransaction(string $transactionId, string $transactionPhase, int $numSegments, int $segmentNumber): self
    {
        $transaction = new self();
        $transaction->id = $transactionId;
        $transaction->phase = $transactionPhase;
        $transaction->numSegments = $numSegments;
        $transaction->segmentNumber = $segmentNumber;

        return $transaction;
    }

    public static function buildTransactionFromOrderData(OrderData $orderData): self
    {
        $transaction = new self();
        $transaction->orderData = $orderData;

        return $transaction;
    }

    public function getPhase(): string
    {
        if ($this->phase === null) {
            throw new EbicsException('phase is null');
        }

        return $this->phase;
    }

    public function getId(): string
    {
        if ($this->id === null) {
            throw new EbicsException('id is null');
        }

        return $this->id;
    }

    public function getSegmentNumber(): int
    {
        if ($this->segmentNumber === null) {
            throw new EbicsException('segmentNumber is null');
        }

        return $this->segmentNumber;
    }

    public function getNumSegments(): int
    {
        if ($this->numSegments === null) {
            throw new EbicsException('numSegments is null');
        }

        return $this->numSegments;
    }

    public function getPlainOrderData(): string
    {
        if ($this->plainOrderData === null) {
            throw new EbicsException('plainOrderData is null');
        }

        return $this->plainOrderData;
    }

    public function getOrderData(): OrderData
    {
        if ($this->orderData === null) {
            throw new EbicsException('orderData is null');
        }

        return $this->orderData;
    }

    public function setPlainOrderData(string $plainOrderData): void
    {
        $this->plainOrderData = $plainOrderData;
    }

    public function setOrderData(OrderData $orderData): void
    {
        $this->orderData = $orderData;
    }
}

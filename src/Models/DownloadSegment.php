<?php

namespace AndrewSvirin\Ebics\Models;

/**
 * Segment item.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class DownloadSegment extends Segment
{
    /**
     * @var string
     */
    private $transactionId;

    /**
     * @var string|null
     */
    private $transactionPhase;

    /**
     * @var string
     */
    private $transactionKey;

    /**
     * @var int|null
     */
    private $segmentNumber;

    /**
     * @var int|null
     */
    private $numSegments;

    /**
     * @var string
     */
    private $orderData;

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function setTransactionId(?string $id): void
    {
        $this->transactionId = $id;
    }

    public function getTransactionPhase(): ?string
    {
        return $this->transactionPhase;
    }

    public function setTransactionPhase(?string $phase): void
    {
        $this->transactionPhase = $phase;
    }

    public function getTransactionKey(): string
    {
        return $this->transactionKey;
    }

    public function setTransactionKey(string $transactionKey): void
    {
        $this->transactionKey = $transactionKey;
    }

    public function getSegmentNumber(): ?int
    {
        return $this->segmentNumber;
    }

    public function setSegmentNumber(?int $segmentNumber): void
    {
        $this->segmentNumber = $segmentNumber;
    }

    public function getNumSegments(): ?int
    {
        return $this->numSegments;
    }

    public function setNumSegments(?int $numSegments): void
    {
        $this->numSegments = $numSegments;
    }

    public function getOrderData(): string
    {
        return $this->orderData;
    }

    public function setOrderData(string $orderData): void
    {
        $this->orderData = $orderData;
    }

    public function isLastSegmentNumber(): bool
    {
        return empty($this->segmentNumber) || ($this->segmentNumber >= $this->numSegments);
    }

    public function getNextSegmentNumber(): int
    {
        if ($this->isLastSegmentNumber()) {
            throw new \RuntimeException('There is a last segment');
        }
        return $this->segmentNumber++;
    }

    public function isLastNextSegmentNumber(): bool
    {
        if ($this->isLastSegmentNumber()) {
            throw new \RuntimeException('There is a last segment');
        }
        return $this->segmentNumber++ >= $this->numSegments;
    }
}

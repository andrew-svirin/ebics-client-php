<?php

namespace AndrewSvirin\Ebics\Models;

/**
 * Segment item.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class UploadSegment extends Segment
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
    private $orderId;

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

    public function setOrderId(?string $orderId): void
    {
        $this->orderId = $orderId;
    }

    public function getOrderId(): ?string
    {
        return $this->orderId;
    }
}

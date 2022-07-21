<?php

namespace AndrewSvirin\Ebics\Models;

use AndrewSvirin\Ebics\Contracts\DownloadTransactionInterface;
use AndrewSvirin\Ebics\Models\Http\Response;

/**
 * Download Transaction represent collection of segments.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class DownloadTransaction extends Transaction implements DownloadTransactionInterface
{
    /**
     * @var DownloadSegment[]
     */
    private array $segments = [];
    private Response $receipt;

    public function getId(): ?string
    {
        $lastSegment = $this->getLastSegment();

        return $lastSegment->getTransactionId();
    }

    public function getKey(): string
    {
        $lastSegment = $this->getLastSegment();

        return $lastSegment->getTransactionKey();
    }

    public function addSegment(DownloadSegment $segment): void
    {
        $this->segments[] = $segment;
    }

    /**
     * @return DownloadSegment[]
     */
    public function getSegments(): array
    {
        return $this->segments;
    }

    public function getLastSegment(): ?DownloadSegment
    {
        if (0 === count($this->segments)) {
            return null;
        }

        return end($this->segments);
    }

    public function getNumSegments(): int
    {
        $lastSegment = $this->getLastSegment();

        return $lastSegment->getNumSegments();
    }

    public function setReceipt(Response $receipt): void
    {
        $this->receipt = $receipt;
    }

    public function getReceipt(): Response
    {
        return $this->receipt;
    }

    public function getOrderData(): string
    {
        $orderData = '';
        foreach ($this->segments as $segment) {
            $orderData .= $segment->getOrderData();
        }

        return $orderData;
    }
}

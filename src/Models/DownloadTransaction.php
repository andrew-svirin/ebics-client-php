<?php

namespace EbicsApi\Ebics\Models;

use EbicsApi\Ebics\Contracts\DownloadTransactionInterface;
use EbicsApi\Ebics\Models\Http\Response;

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
    private string $orderData;

    public function getId(): ?string
    {
        $lastSegment = $this->getLastSegment();

        if (empty($lastSegment)) {
            return null;
        }

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

    public function setOrderData(string $orderData): string
    {
        return $this->orderData = $orderData;
    }

    public function getOrderData(): string
    {
        return $this->orderData;
    }
}

<?php

namespace EbicsApi\Ebics\Contexts;

/**
 * Class HVTContext context container for HVT orders
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class HVTContext extends BTFContext
{
    private string $orderId;
    private string $partnerId;
    private string $orderType;
    private bool $completeOrderData;
    private int $fetchLimit;
    private int $fetchOffset;

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function setOrderId(string $orderId): HVTContext
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getPartnerId(): string
    {
        return $this->partnerId;
    }

    public function setPartnerId(string $partnerId): HVTContext
    {
        $this->partnerId = $partnerId;

        return $this;
    }

    public function getOrderType(): string
    {
        return $this->orderType;
    }

    public function setOrderType(string $orderType): HVTContext
    {
        $this->orderType = $orderType;

        return $this;
    }

    public function getCompleteOrderData(): bool
    {
        return $this->completeOrderData;
    }

    public function setCompleteOrderData(bool $completeOrderData): HVTContext
    {
        $this->completeOrderData = $completeOrderData;

        return $this;
    }

    public function getFetchLimit(): int
    {
        return $this->fetchLimit;
    }

    public function setFetchLimit(int $fetchLimit): HVTContext
    {
        $this->fetchLimit = $fetchLimit;

        return $this;
    }

    public function getFetchOffset(): int
    {
        return $this->fetchOffset;
    }

    public function setFetchOffset(int $fetchOffset): HVTContext
    {
        $this->fetchOffset = $fetchOffset;

        return $this;
    }
}

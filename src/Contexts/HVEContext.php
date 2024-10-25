<?php

namespace AndrewSvirin\Ebics\Contexts;

/**
 * Class HVEContext context container for HVE orders
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class HVEContext extends BTFContext
{
    private string $orderId;
    private string $partnerId;
    private string $orderType;
    private string $digest;

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function setOrderId(string $orderId): HVEContext
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getPartnerId(): string
    {
        return $this->partnerId;
    }

    public function setPartnerId(string $partnerId): HVEContext
    {
        $this->partnerId = $partnerId;

        return $this;
    }

    public function getOrderType(): string
    {
        return $this->orderType;
    }

    public function setOrderType(string $orderType): HVEContext
    {
        $this->orderType = $orderType;

        return $this;
    }

    public function getDigest(): string
    {
        return $this->digest;
    }

    public function setDigest(string $digest): HVEContext
    {
        $this->digest = $digest;

        return $this;
    }
}

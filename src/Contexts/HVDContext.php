<?php

namespace AndrewSvirin\Ebics\Contexts;

/**
 * Class HVDContext context container for HVD orders
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class HVDContext extends BTFContext
{
    /**
     * @var string
     */
    private $orderId;

    /**
     * @var string
     */
    private $partnerId;

    /**
     * @var string
     */
    private $orderType;

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function setOrderId(string $orderId): HVDContext
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getPartnerId(): string
    {
        return $this->partnerId;
    }

    public function setPartnerId(string $partnerId): HVDContext
    {
        $this->partnerId = $partnerId;

        return $this;
    }

    public function getOrderType(): string
    {
        return $this->orderType;
    }

    public function setOrderType(string $orderType): HVDContext
    {
        $this->orderType = $orderType;

        return $this;
    }
}

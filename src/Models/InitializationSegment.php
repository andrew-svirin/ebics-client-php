<?php

namespace AndrewSvirin\Ebics\Models;

/**
 * Initialization Segment item.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class InitializationSegment extends Segment
{
    /**
     * @var string
     */
    private $orderData;

    public function getOrderData(): string
    {
        return $this->orderData;
    }

    public function setOrderData(string $orderData): void
    {
        $this->orderData = $orderData;
    }
}

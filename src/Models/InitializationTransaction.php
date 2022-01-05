<?php

namespace AndrewSvirin\Ebics\Models;

use AndrewSvirin\Ebics\Contracts\InitializationTransactionInterface;

/**
 * Download Transaction represent collection of segments.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class InitializationTransaction extends Transaction implements InitializationTransactionInterface
{
    /**
     * @var InitializationSegment
     */
    private $initializationSegment;

    public function getKey(): string
    {
        return $this->initializationSegment->getTransactionKey();
    }

    public function getOrderData(): string
    {
        return $this->initializationSegment->getOrderData();
    }

    public function getInitializationSegment(): InitializationSegment
    {
        return $this->initializationSegment;
    }

    public function setInitializationSegment(InitializationSegment $initializationSegment): void
    {
        $this->initializationSegment = $initializationSegment;
    }
}

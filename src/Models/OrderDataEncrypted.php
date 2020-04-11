<?php

namespace AndrewSvirin\Ebics\Models;

/**
 * Class OrderDataEncrypted represents OrderData Response model.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class OrderDataEncrypted
{
    /**
     * @var string
     */
    private $orderData;

    /**
     * @var string Binary value.
     */
    private $transactionKey;

    public function __construct(string $orderData, string $transactionKey)
    {
        $this->orderData = $orderData;
        $this->transactionKey = $transactionKey;
    }

    /**
     * @return string
     */
    public function getOrderData(): string
    {
        return $this->orderData;
    }

    /**
     * @return string
     */
    public function getTransactionKey(): string
    {
        return $this->transactionKey;
    }
}

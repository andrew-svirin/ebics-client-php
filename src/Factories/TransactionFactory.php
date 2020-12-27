<?php

namespace AndrewSvirin\Ebics\Factories;

use AndrewSvirin\Ebics\Models\OrderData;
use AndrewSvirin\Ebics\Models\Transaction;

/**
 * Class OrderDataFactory represents producers for the @see OrderData.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class TransactionFactory
{
    /**
     * Build Transactions from arguments.
     *
     * @param string $transactionId
     * @param string $transactionPhase
     * @param string $numSegments
     * @param string $segmentNumber
     *
     * @return Transaction
     */
    public static function buildTransaction(
        string $transactionId,
        string $transactionPhase,
        string $numSegments,
        string $segmentNumber
    ): Transaction {
        $transaction = new Transaction();
        $transaction->setId($transactionId);
        $transaction->setPhase($transactionPhase);
        $transaction->setNumSegments((int)$numSegments);
        $transaction->setSegmentNumber((int)$segmentNumber);

        return $transaction;
    }

    /**
     * Build empty Transaction.
     *
     * @param OrderData $orderData
     *
     * @return Transaction
     */
    public static function buildTransactionFromOrderData(OrderData $orderData): Transaction
    {
        $transaction = new Transaction();
        $transaction->setOrderData($orderData);

        return $transaction;
    }
}

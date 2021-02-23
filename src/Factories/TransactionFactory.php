<?php

namespace AndrewSvirin\Ebics\Factories;

use AndrewSvirin\Ebics\Models\Transaction;

/**
 * Class TransactionFactory represents producers for the @see Transaction.
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
     *
     * @return Transaction
     */
    public function create(string $transactionId, string $transactionPhase): Transaction
    {
        $transaction = new Transaction();
        $transaction->setId($transactionId);
        $transaction->setPhase($transactionPhase);

        return $transaction;
    }
}

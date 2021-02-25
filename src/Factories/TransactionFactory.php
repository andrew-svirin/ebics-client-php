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
     * @return Transaction
     */
    public function create(): Transaction
    {
        $transaction = new Transaction();

        return $transaction;
    }
}

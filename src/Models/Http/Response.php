<?php

namespace AndrewSvirin\Ebics\Models\Http;

use AndrewSvirin\Ebics\Models\Data;
use AndrewSvirin\Ebics\Models\Transaction;

/**
 * Response model represents Response model.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class Response extends Data
{
    /**
     * @var Transaction[]
     */
    private $transactions = [];

    public function addTransaction(Transaction $transaction): void
    {
        $this->transactions[] = $transaction;
    }

    /**
     * @return Transaction[]
     */
    public function getTransactions(): array
    {
        return $this->transactions;
    }

    /**
     * @return Transaction|null
     */
    public function getLastTransaction(): ?Transaction
    {
        if (0 === count($this->transactions)) {
            return null;
        }

        return end($this->transactions);
    }
}

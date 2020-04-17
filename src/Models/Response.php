<?php

namespace AndrewSvirin\Ebics\Models;

/**
 * Response model represents Response model.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class Response extends DOMDocument
{
    /**
     * @var Transaction[]
     */
    private $transactions = [];

    public function addTransaction(Transaction $transaction)
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

    public function getLastTransaction(): ?Transaction
    {
        if (0 === \count($this->transactions)) {
            return null;
        }

        return end($this->transactions);
    }
}

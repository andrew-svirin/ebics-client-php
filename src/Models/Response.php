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
    /**
     * @var string
     */
    private $versionI;

    public function __construct(string $content = null, string $version = Version::V25)
    {
        parent::__construct();

        if ($content) {
            $this->loadXML($content);
        }
        $this->versionI = $version;
    }

    public function addTransaction(Transaction $transaction) : void
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
     * @return string
     */
    public function getVersion(): string
    {
        return $this->versionI;
    }

    public function getLastTransaction(): ?Transaction
    {
        if (0 === count($this->transactions)) {
            return null;
        }

        return end($this->transactions);
    }
}

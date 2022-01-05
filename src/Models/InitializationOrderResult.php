<?php

namespace AndrewSvirin\Ebics\Models;

/**
 * Order result with extracted data.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class InitializationOrderResult extends OrderResult
{
    /**
     * @var Document
     */
    private $dataDocument;

    /**
     * @var InitializationTransaction
     */
    private $transaction;

    public function setDataDocument(Document $document): void
    {
        $this->dataDocument = $document;
    }

    public function getDataDocument(): ?Document
    {
        return $this->dataDocument;
    }

    public function setTransaction(InitializationTransaction $transaction): void
    {
        $this->transaction = $transaction;
    }

    public function getTransaction(): InitializationTransaction
    {
        return $this->transaction;
    }
}

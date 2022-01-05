<?php

namespace AndrewSvirin\Ebics\Models;

use AndrewSvirin\Ebics\Contracts\OrderDataInterface;

/**
 * Order result with extracted data.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class UploadOrderResult extends OrderResult
{
    /**
     * @var UploadTransaction
     */
    private $transaction;

    /**
     * @var OrderDataInterface
     */
    private $dataDocument;

    public function setTransaction(UploadTransaction $transaction): void
    {
        $this->transaction = $transaction;
    }

    public function getTransaction(): UploadTransaction
    {
        return $this->transaction;
    }

    public function setDataDocument(OrderDataInterface $document): void
    {
        $this->dataDocument = $document;
    }

    public function getDataDocument(): ?OrderDataInterface
    {
        return $this->dataDocument;
    }
}

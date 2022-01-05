<?php

namespace AndrewSvirin\Ebics\Models;

/**
 * Order result with extracted data.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class DownloadOrderResult extends OrderResult
{
    /**
     * @var array
     */
    private $dataFiles;

    /**
     * @var Document
     */
    private $dataDocument;

    /**
     * @var DownloadTransaction
     */
    private $transaction;

    public function setDataFiles(array $dataFiles): void
    {
        $this->dataFiles = $dataFiles;
    }

    public function getDataFiles(): ?array
    {
        return $this->dataFiles;
    }

    public function setDataDocument(Document $document): void
    {
        $this->dataDocument = $document;
    }

    public function getDataDocument(): ?Document
    {
        return $this->dataDocument;
    }

    public function setTransaction(DownloadTransaction $transaction): void
    {
        $this->transaction = $transaction;
    }

    public function getTransaction(): DownloadTransaction
    {
        return $this->transaction;
    }
}

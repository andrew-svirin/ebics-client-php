<?php

namespace EbicsApi\Ebics\Models;

/**
 * Order result with extracted data.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class DownloadOrderResult extends OrderResult
{
    private array $dataFiles;
    private Document $dataDocument;
    private DownloadTransaction $transaction;

    /**
     * @param Document[]|string[] $dataFiles
     *
     * @return void
     */
    public function setDataFiles(array $dataFiles): void
    {
        $this->dataFiles = $dataFiles;
    }

    /**
     * @return Document[]|string[]|null
     */
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

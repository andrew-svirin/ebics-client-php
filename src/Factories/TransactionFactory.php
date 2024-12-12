<?php

namespace EbicsApi\Ebics\Factories;

use EbicsApi\Ebics\Models\DownloadTransaction;
use EbicsApi\Ebics\Models\InitializationTransaction;
use EbicsApi\Ebics\Models\UploadTransaction;

/**
 * Class TransactionFactory represents producers for the @see DownloadTransaction.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class TransactionFactory
{
    public function createDownloadTransaction(): DownloadTransaction
    {
        return new DownloadTransaction();
    }

    public function createUploadTransaction(): UploadTransaction
    {
        return new UploadTransaction();
    }

    public function createInitializationTransaction(): InitializationTransaction
    {
        return new InitializationTransaction();
    }
}

<?php

namespace AndrewSvirin\Ebics\Factories;

use AndrewSvirin\Ebics\Models\DownloadTransaction;
use AndrewSvirin\Ebics\Models\InitializationTransaction;
use AndrewSvirin\Ebics\Models\UploadTransaction;

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

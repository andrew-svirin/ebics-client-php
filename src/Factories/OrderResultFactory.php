<?php

namespace EbicsApi\Ebics\Factories;

use EbicsApi\Ebics\Models\DownloadOrderResult;
use EbicsApi\Ebics\Models\InitializationOrderResult;
use EbicsApi\Ebics\Models\UploadOrderResult;

/**
 * Class SegmentFactory represents producers for the @see OrderResult.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class OrderResultFactory
{
    public function createInitializationOrderResult(): InitializationOrderResult
    {
        return new InitializationOrderResult();
    }

    public function createDownloadOrderResult(): DownloadOrderResult
    {
        return new DownloadOrderResult();
    }

    public function createUploadOrderResult(): UploadOrderResult
    {
        return new UploadOrderResult();
    }
}

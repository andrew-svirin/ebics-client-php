<?php

namespace AndrewSvirin\Ebics\Factories;

use AndrewSvirin\Ebics\Models\DownloadOrderResult;
use AndrewSvirin\Ebics\Models\InitializationOrderResult;
use AndrewSvirin\Ebics\Models\UploadOrderResult;

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

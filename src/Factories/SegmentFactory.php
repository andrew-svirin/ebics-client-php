<?php

namespace AndrewSvirin\Ebics\Factories;

use AndrewSvirin\Ebics\Models\DownloadSegment;
use AndrewSvirin\Ebics\Models\InitializationSegment;
use AndrewSvirin\Ebics\Models\Segment;
use AndrewSvirin\Ebics\Models\TransferSegment;
use AndrewSvirin\Ebics\Models\UploadSegment;

/**
 * Class SegmentFactory represents producers for the @see Segment.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class SegmentFactory
{
    public function createInitializationSegment(): InitializationSegment
    {
        return new InitializationSegment();
    }

    public function createDownloadSegment(): DownloadSegment
    {
        return new DownloadSegment();
    }

    public function createUploadSegment(): UploadSegment
    {
        return new UploadSegment();
    }

    public function createTransferSegment(): TransferSegment
    {
        return new TransferSegment();
    }
}

<?php

namespace EbicsApi\Ebics\Factories;

use EbicsApi\Ebics\Models\DownloadSegment;
use EbicsApi\Ebics\Models\InitializationSegment;
use EbicsApi\Ebics\Models\Segment;
use EbicsApi\Ebics\Models\TransferSegment;
use EbicsApi\Ebics\Models\UploadSegment;

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

<?php

namespace AndrewSvirin\Ebics\Factories;

use AndrewSvirin\Ebics\Contexts\BTFContext;
use AndrewSvirin\Ebics\Contexts\BTUContext;
use AndrewSvirin\Ebics\Models\Http\Request;
use AndrewSvirin\Ebics\Models\UploadTransaction;
use DateTimeInterface;
use LogicException;

/**
 * Ebics 2.x RequestFactory.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
abstract class RequestFactoryV2 extends RequestFactory
{
    public function createBTD(
        DateTimeInterface $dateTime,
        BTFContext $btfContext,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null,
        int $segmentNumber = null,
        bool $isLastSegment = null
    ): Request {
        throw new LogicException('Method for EBICS 3.0');
    }

    public function createBTU(
        BTUContext $btuContext,
        DateTimeInterface $dateTime,
        UploadTransaction $transaction
    ): Request {
        throw new LogicException('Method for EBICS 3.0');
    }
}

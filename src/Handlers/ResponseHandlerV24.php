<?php

namespace EbicsApi\Ebics\Handlers;

use EbicsApi\Ebics\Handlers\Traits\H003Trait;
use EbicsApi\Ebics\Models\Http\Request;
use EbicsApi\Ebics\Models\Http\Response;
use EbicsApi\Ebics\Models\UploadSegment;

/**
 * Ebics 2.4 ResponseHandler.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class ResponseHandlerV24 extends ResponseHandler
{
    use H003Trait;

    /**
     * Extract DownloadSegment from the DOM XML.
     */
    public function extractUploadSegment(Request $request, Response $response): UploadSegment
    {
        $transactionId = $this->retrieveH00XTransactionId($response);
        $transactionPhase = $this->retrieveH00XTransactionPhase($response);
        $orderId = $this->retrieveH00XRequestOrderId($request);

        $segment = $this->segmentFactory->createUploadSegment();
        $segment->setResponse($response);
        $segment->setTransactionId($transactionId);
        $segment->setTransactionPhase($transactionPhase);
        $segment->setOrderId($orderId);

        return $segment;
    }
}

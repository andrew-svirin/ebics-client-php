<?php

namespace AndrewSvirin\Ebics\Handlers;

use AndrewSvirin\Ebics\Handlers\Traits\H005Trait;
use AndrewSvirin\Ebics\Models\Http\Request;
use AndrewSvirin\Ebics\Models\Http\Response;
use AndrewSvirin\Ebics\Models\UploadSegment;

/**
 * Ebics 3.0 ResponseHandler.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class ResponseHandlerV3 extends ResponseHandler
{
    use H005Trait;

    /**
     * Extract DownloadSegment from the DOM XML.
     */
    public function extractUploadSegment(Request $request, Response $response): UploadSegment
    {
        $transactionId = $this->retrieveH00XTransactionId($response);
        $transactionPhase = $this->retrieveH00XTransactionPhase($response);
        $orderId = $this->retrieveH00XResponseOrderId($response);

        $segment = $this->segmentFactory->createUploadSegment();
        $segment->setResponse($response);
        $segment->setTransactionId($transactionId);
        $segment->setTransactionPhase($transactionPhase);
        $segment->setOrderId($orderId);

        return $segment;
    }
}

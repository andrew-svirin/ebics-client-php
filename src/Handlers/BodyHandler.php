<?php

namespace AndrewSvirin\Ebics\Handlers;

use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Models\Request;
use DOMDocument;
use DOMElement;

/**
 * Class BodyHandler manage body DOM elements.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class BodyHandler
{
    /**
     * @var bool
     */
    private $compress;

    /**
     * @var bool
     */
    private $encode;

    public function __construct()
    {
        $this->compress = true;
        $this->encode = true;
    }

    /**
     * Add body and children elements to request.
     */
    public function handle(Request $request, DOMElement $xmlRequest, string $orderData): Request
    {
        // Add body to request.
        $xmlBody = $request->createElement('body');
        $xmlRequest->appendChild($xmlBody);

        // Add DataTransfer to body.
        $xmlDataTransfer = $request->createElement('DataTransfer');
        $xmlBody->appendChild($xmlDataTransfer);

        // Add OrderData to DataTransfer.
        $xmlOrderData = $request->createElement('OrderData');
        if ($this->compress) {
            // Try to compress to gz order data.
            if (!($orderData = gzcompress($orderData))) {
                throw new EbicsException('Order Data were compressed wrongly.');
            }
        }
        if ($this->encode) {
            $orderData = base64_encode($orderData);
        }
        $xmlOrderData->nodeValue = $orderData;
        $xmlDataTransfer->appendChild($xmlOrderData);

        return $request;
    }

    /**
     * Add body and children elements to transfer request.
     */
    public function handleTransferReceipt(Request $request, DOMElement $xmlRequest, int $receiptCode): Request
    {
        // Add body to request.
        $xmlBody = $request->createElement('body');
        $xmlRequest->appendChild($xmlBody);

        // Add TransferReceipt to body.
        $xmlTransferReceipt = $request->createElement('TransferReceipt');
        $xmlTransferReceipt->setAttribute('authenticate', 'true');
        $xmlBody->appendChild($xmlTransferReceipt);

        // Add ReceiptCode to TransferReceipt.
        $xmlReceiptCode = $request->createElement('ReceiptCode');
        $xmlReceiptCode->nodeValue = (string)$receiptCode;
        $xmlTransferReceipt->appendChild($xmlReceiptCode);

        return $request;
    }

    /**
     * Add empty body element to request.
     */
    public function handleEmpty(Request $request, DOMElement $xmlRequest): Request
    {
        // Add body to request.
        $xmlBody = $request->createElement('body');
        $xmlRequest->appendChild($xmlBody);

        return $request;
    }
}

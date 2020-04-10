<?php

namespace AndrewSvirin\Ebics\Handlers;

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
     *
     * @param string $orderData
     */
    public function handle(DOMDocument $xml, DOMElement $xmlRequest, $orderData)
    {
        // Add body to request.
        $xmlBody = $xml->createElement('body');
        $xmlRequest->appendChild($xmlBody);

        // Add DataTransfer to body.
        $xmlDataTransfer = $xml->createElement('DataTransfer');
        $xmlBody->appendChild($xmlDataTransfer);

        // Add OrderData to DataTransfer.
        $xmlOrderData = $xml->createElement('OrderData');
        if ($this->compress) {
            $orderData = gzcompress($orderData);
        }
        if ($this->encode) {
            $orderData = base64_encode($orderData);
        }
        $xmlOrderData->nodeValue = $orderData;
        $xmlDataTransfer->appendChild($xmlOrderData);
    }

    /**
     * Add body and children elements to request.
     */
    public function handleTransferReceipt(DOMDocument $xml, DOMElement $xmlRequest, int $receiptCode)
    {
        // Add body to request.
        $xmlBody = $xml->createElement('body');
        $xmlRequest->appendChild($xmlBody);

        // Add TransferReceipt to body.
        $xmlTransferReceipt = $xml->createElement('TransferReceipt');
        $xmlTransferReceipt->setAttribute('authenticate', 'true');
        $xmlBody->appendChild($xmlTransferReceipt);

        // Add ReceiptCode to TransferReceipt.
        $xmlReceiptCode = $xml->createElement('ReceiptCode');
        $xmlReceiptCode->nodeValue = $receiptCode;
        $xmlTransferReceipt->appendChild($xmlReceiptCode);
    }

    /**
     * Add empty body element to request.
     */
    public function handleEmpty(DOMDocument $xml, DOMElement $xmlRequest)
    {
        // Add body to request.
        $xmlBody = $xml->createElement('body');
        $xmlRequest->appendChild($xmlBody);
    }
}

<?php

namespace AndrewSvirin\Ebics\Handlers;

use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Handlers\Traits\XPathTrait;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

/**
 * Class BodyHandler manage body DOM elements.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class BodyHandler
{

    use XPathTrait;

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
     * @param DOMDocument $xml
     * @param DOMElement $xmlRequest
     * @param string $orderData
     *
     * @throws EbicsException
     */
    public function handle(DOMDocument $xml, DOMElement $xmlRequest, string $orderData): void
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
    }

    /**
     * Add body and children elements to transfer request.
     *
     * @param DOMDocument $xml
     * @param DOMElement $xmlRequest
     * @param int $receiptCode
     */
    public function handleTransferReceipt(DOMDocument $xml, DOMElement $xmlRequest, int $receiptCode): void
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
        $xmlReceiptCode->nodeValue = (string)$receiptCode;
        $xmlTransferReceipt->appendChild($xmlReceiptCode);
    }

    /**
     * Add empty body element to request.
     *
     * @param DOMDocument $request
     * @param DOMNode|null $xmlRequestHeader
     */
    public function handleEmpty(DOMDocument $request, DOMNode $xmlRequestHeader = null): void
    {
        // Create body element.
        $xmlBody = $request->createElement('body');

        // Find Header element to insert after.
        if (null === $xmlRequestHeader) {
            $xpath = new DOMXpath($request);
            $xmlRequestHeader = $xpath->query('//header')->item(0);
        }

        $this->insertAfter($xmlBody, $xmlRequestHeader);
    }
}

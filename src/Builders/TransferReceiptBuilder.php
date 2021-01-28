<?php

namespace AndrewSvirin\Ebics\Builders;

use DOMDocument;
use DOMElement;

/**
 * Class TransferReceiptBuilder builder for request container.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class TransferReceiptBuilder
{

    // The value of the acknowledgement is 0 (“positive acknowledgement”)
    // if download and processing of the order data was successful
    const CODE_RECEIPT_POSITIVE ='0';

    // Otherwise the value of the acknowledgement is 1 (“negative acknowledgement”).
    const CODE_RECEIPT_NEGATIVE = '1';

    /**
     * @var DOMElement
     */
    private $instance;

    /**
     * @var DOMDocument
     */
    private $dom;

    public function __construct(DOMDocument $dom = null)
    {
        $this->dom = $dom;
    }

    public function createInstance(): TransferReceiptBuilder
    {
        $this->instance = $this->dom->createElement('TransferReceipt');
        $this->instance->setAttribute('authenticate', 'true');

        return $this;
    }

    public function addReceiptCode(string $receiptCode): TransferReceiptBuilder
    {
        $xmlReceiptCode = $this->dom->createElement('ReceiptCode');
        $xmlReceiptCode->nodeValue = $receiptCode;
        $this->instance->appendChild($xmlReceiptCode);

        return $this;
    }

    public function getInstance(): DOMElement
    {
        return $this->instance;
    }
}

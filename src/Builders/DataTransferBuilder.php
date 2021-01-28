<?php

namespace AndrewSvirin\Ebics\Builders;

use AndrewSvirin\Ebics\Exceptions\EbicsException;
use DOMDocument;
use DOMElement;

/**
 * Class DataTransferBuilder builder for request container.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class DataTransferBuilder
{

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

    public function createInstance(): DataTransferBuilder
    {
        $this->instance = $this->dom->createElement('DataTransfer');

        return $this;
    }

    /**
     * @param string $orderData
     *
     * @return $this
     * @throws EbicsException
     */
    public function addOrderData(string $orderData): DataTransferBuilder
    {
        $xmlDataTransfer = $this->dom->createElement('OrderData');

        // Try to compress to gz order data.
        if (!($orderData = gzcompress($orderData))) {
            throw new EbicsException('Order Data were compressed wrongly.');
        }
        $orderData = base64_encode($orderData);
        $xmlDataTransfer->nodeValue = $orderData;

        $this->instance->appendChild($xmlDataTransfer);

        return $this;
    }

    public function getInstance(): DOMElement
    {
        return $this->instance;
    }
}

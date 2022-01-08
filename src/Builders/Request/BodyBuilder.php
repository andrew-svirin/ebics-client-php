<?php

namespace AndrewSvirin\Ebics\Builders\Request;

use Closure;
use DOMDocument;
use DOMElement;

/**
 * Class BodyBuilder builder for request container.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class BodyBuilder
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

    public function createInstance(): BodyBuilder
    {
        $this->instance = $this->dom->createElement('body');

        return $this;
    }

    public function addDataTransfer(Closure $callback): BodyBuilder
    {
        $dataTransferBuilder = new DataTransferBuilder($this->dom);
        $this->instance->appendChild($dataTransferBuilder->createInstance()->getInstance());

        call_user_func($callback, $dataTransferBuilder);

        return $this;
    }

    public function addTransferReceipt(Closure $callback): BodyBuilder
    {
        $transferReceiptBuilder = new TransferReceiptBuilder($this->dom);
        $this->instance->appendChild($transferReceiptBuilder->createInstance()->getInstance());

        call_user_func($callback, $transferReceiptBuilder);

        return $this;
    }

    public function getInstance(): DOMElement
    {
        return $this->instance;
    }
}

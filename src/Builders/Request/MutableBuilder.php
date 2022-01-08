<?php

namespace AndrewSvirin\Ebics\Builders\Request;

use DOMDocument;
use DOMElement;

/**
 * Class MutableBuilder builder for request container.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class MutableBuilder
{
    const PHASE_INITIALIZATION = 'Initialisation';
    const PHASE_RECEIPT = 'Receipt';
    const PHASE_TRANSFER = 'Transfer';

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

    /**
     * Create body for UnsecuredRequest.
     *
     * @return $this
     */
    public function createInstance(): MutableBuilder
    {
        $this->instance = $this->dom->createElement('mutable');

        return $this;
    }

    public function addTransactionPhase(string $transactionPhase): MutableBuilder
    {
        $xmlTransactionPhase = $this->dom->createElement('TransactionPhase');
        $xmlTransactionPhase->nodeValue = $transactionPhase;

        $this->instance->appendChild($xmlTransactionPhase);

        return $this;
    }

    public function addSegmentNumber(int $segmentNumber = null, ?bool $isLastSegment = null): MutableBuilder
    {
        if (null !== $segmentNumber) {
            $xmlSegmentNumber = $this->dom->createElement('SegmentNumber');
            if ($isLastSegment) {
                $xmlSegmentNumber->setAttribute('lastSegment', 'true');
            }
            $xmlSegmentNumber->nodeValue = (string)$segmentNumber;
            $this->instance->appendChild($xmlSegmentNumber);
        }

        return $this;
    }

    public function getInstance(): DOMElement
    {
        return $this->instance;
    }
}

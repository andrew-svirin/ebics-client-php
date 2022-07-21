<?php

namespace AndrewSvirin\Ebics\Builders\Request;

use Closure;
use DOMDocument;
use DOMElement;

/**
 * Class XmlBuilder builder for request container.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
abstract class XmlBuilder
{
    const EBICS_REQUEST = 'ebicsRequest';
    const EBICS_UNSECURED_REQUEST = 'ebicsUnsecuredRequest';
    const EBICS_NO_PUB_KEY_DIGESTS = 'ebicsNoPubKeyDigestsRequest';
    const EBICS_HEV = 'ebicsHEVRequest';

    protected DOMElement $instance;
    protected ?DOMDocument $dom;

    public function __construct(?DOMDocument $dom = null)
    {
        $this->dom = $dom;
    }

    abstract public function createUnsecured(): XmlBuilder;

    abstract public function createSecuredNoPubKeyDigests(): XmlBuilder;

    abstract public function createSecured(): XmlBuilder;

    public function createHEV(): XmlBuilder
    {
        return $this->createH000(self::EBICS_HEV);
    }

    private function createH000(string $container): XmlBuilder
    {
        $this->instance = $this->dom->createElementNS('http://www.ebics.org/H000', $container);
        $this->instance->setAttributeNS(
            'http://www.w3.org/2001/XMLSchema-instance',
            'xsi:schemaLocation',
            'http://www.ebics.org/H000 http://www.ebics.org/H000/ebics_hev.xsd'
        );

        return $this;
    }

    abstract public function addHeader(Closure $callback): XmlBuilder;

    abstract public function addBody(Closure $callback = null): XmlBuilder;

    public function addHostId(string $hostId): XmlBuilder
    {
        $xmlHostId = $this->dom->createElement('HostID');
        $xmlHostId->nodeValue = $hostId;
        $this->instance->appendChild($xmlHostId);

        return $this;
    }

    public function getInstance(): DOMElement
    {
        return $this->instance;
    }
}

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
    private const EBICS_REQUEST = 'ebicsRequest';
    private const EBICS_UNSECURED_REQUEST = 'ebicsUnsecuredRequest';
    private const EBICS_UNSIGNED_REQUEST = 'ebicsUnsignedRequest';
    private const EBICS_NO_PUB_KEY_DIGESTS = 'ebicsNoPubKeyDigestsRequest';
    private const EBICS_HEV = 'ebicsHEVRequest';

    protected DOMElement $instance;
    protected ?DOMDocument $dom;

    public function __construct(?DOMDocument $dom = null)
    {
        $this->dom = $dom;
    }

    public function createUnsecured(): XmlBuilder
    {
        return $this->createH00X(self::EBICS_UNSECURED_REQUEST);
    }

    public function createSecuredNoPubKeyDigests(): XmlBuilder
    {
        return $this->createH00X(self::EBICS_NO_PUB_KEY_DIGESTS, true);
    }

    public function createSecured(): XmlBuilder
    {
        return $this->createH00X(self::EBICS_REQUEST, true);
    }

    public function createUnsigned(): XmlBuilder
    {
        return $this->createH00X(self::EBICS_UNSIGNED_REQUEST);
    }

    abstract protected function getH00XVersion(): string;

    abstract protected function getH00XNamespace(): string;

    private function createH00X(string $container, bool $secured = false): XmlBuilder
    {
        $this->instance = $this->dom->createElementNS($this->getH00XNamespace(), $container);
        if ($secured) {
            $this->instance->setAttributeNS(
                'http://www.w3.org/2000/xmlns/',
                'xmlns:ds',
                'http://www.w3.org/2000/09/xmldsig#'
            );
        }
        $this->instance->setAttribute('Version', $this->getH00XVersion());
        $this->instance->setAttribute('Revision', '1');

        return $this;
    }

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

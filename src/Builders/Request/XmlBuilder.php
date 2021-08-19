<?php

namespace AndrewSvirin\Ebics\Builders\Request;

use AndrewSvirin\Ebics\EbicsClient;
use Closure;
use DOMDocument;
use DOMElement;

/**
 * Class XmlBuilder builder for request container.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class XmlBuilder
{
    const EBICS_REQUEST = 'ebicsRequest';
    const EBICS_UNSECURED_REQUEST = 'ebicsUnsecuredRequest';
    const EBICS_NO_PUB_KEY_DIGESTS = 'ebicsNoPubKeyDigestsRequest';
    const EBICS_HEV = 'ebicsHEVRequest';

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

    public function createUnsecured(string $ebicsVersion = EbicsClient::VERSION_25): XmlBuilder
    {
        switch ($ebicsVersion) {
            case EbicsClient::VERSION_30:
                $this->createH005(self::EBICS_UNSECURED_REQUEST);
                break;
            
            case EbicsClient::VERSION_25:
            default:
                $this->createH004(self::EBICS_UNSECURED_REQUEST);
                break;
        }

        return $this;
    }

    public function createSecuredNoPubKeyDigests(string $ebicsVersion = EbicsClient::VERSION_25): XmlBuilder
    {
        $this->createH005(self::EBICS_NO_PUB_KEY_DIGESTS, true);
        switch ($ebicsVersion) {
            case EbicsClient::VERSION_30:
                $this->createH005(self::EBICS_NO_PUB_KEY_DIGESTS, true);
                break;
            
            case EbicsClient::VERSION_25:
            default:
            $this->createH004(self::EBICS_NO_PUB_KEY_DIGESTS, true);
                break;
        }

        return $this;
    }

    public function createSecured(string $ebicsVersion = EbicsClient::VERSION_25): XmlBuilder
    {
        $this->createH005(self::EBICS_REQUEST, true);
        switch ($ebicsVersion) {
            case EbicsClient::VERSION_30:
                $this->createH005(self::EBICS_REQUEST, true);
                break;
            
            case EbicsClient::VERSION_25:
            default:
                $this->createH004(self::EBICS_REQUEST, true);
                break;
        }

        return $this;
    }

    private function createH004(string $container, bool $secured = false): XmlBuilder
    {
        $this->instance = $this->dom->createElementNS('urn:org:ebics:H004', $container);
        if ($secured) {
            $this->instance->setAttributeNS(
                'http://www.w3.org/2000/xmlns/',
                'xmlns:ds',
                'http://www.w3.org/2000/09/xmldsig#'
            );
        }
        $this->instance->setAttribute('Version', 'H004');
        $this->instance->setAttribute('Revision', '1');

        return $this;
    }

    private function createH005(string $container, bool $secured = false): XmlBuilder
    {
        $this->instance = $this->dom->createElementNS('urn:org:ebics:H005', $container);
        if ($secured) {
            $this->instance->setAttributeNS(
                'http://www.w3.org/2000/xmlns/',
                'xmlns:ds',
                'http://www.w3.org/2000/09/xmldsig#'
            );
        }
        $this->instance->setAttribute('Version', 'H005');
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

    public function addHeader(Closure $callback): XmlBuilder
    {
        $headerBuilder = new HeaderBuilder($this->dom);
        $header = $headerBuilder->createInstance()->getInstance();
        $this->instance->appendChild($header);

        call_user_func($callback, $headerBuilder);

        return $this;
    }

    public function addBody(Closure $callback = null): XmlBuilder
    {
        $bodyBuilder = new BodyBuilder($this->dom);
        $body = $bodyBuilder->createInstance()->getInstance();
        $this->instance->appendChild($body);

        if (null !== $callback) {
            call_user_func($callback, $bodyBuilder);
        }

        return $this;
    }

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

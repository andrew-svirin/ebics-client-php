<?php

namespace AndrewSvirin\Ebics\Handlers;

use AndrewSvirin\Ebics\Models\Bank;
use DOMDocument;
use DOMElement;

/**
 * Class Host manages header DOM elements.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class HostHandler
{
    /**
     * @var Bank
     */
    private $bank;

    public function __construct(Bank $bank)
    {
        $this->bank = $bank;
    }

    /**
     * Add HostID for Request XML.
     */
    public function handle(DOMDocument $xml, DOMElement $xmlRequest): void
    {
        // Add HostID to Request.
        $xmlHostId = $xml->createElement('HostID');
        $xmlHostId->nodeValue = $this->bank->getHostId();
        $xmlRequest->appendChild($xmlHostId);
    }
}

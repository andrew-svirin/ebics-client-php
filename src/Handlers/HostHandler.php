<?php

namespace AndrewSvirin\Ebics\Handlers;

use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\Request;
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
     * Add HostID for Request XML.
     */
    public function handle(Bank $bank, Request $request, DOMElement $xmlRequest) : Request
    {
        // Add HostID to Request.
        $xmlHostId = $request->createElement('HostID');
        $xmlHostId->nodeValue = $bank->getHostId();
        $xmlRequest->appendChild($xmlHostId);

        return $request;
    }
}

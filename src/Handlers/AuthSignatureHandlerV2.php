<?php

namespace AndrewSvirin\Ebics\Handlers;

use AndrewSvirin\Ebics\Handlers\Traits\XPathTrait;
use DOMDocument;
use DOMXPath;

/**
 * Ebics 2.5 AuthSignatureHandler.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @internal
 */
class AuthSignatureHandlerV2 extends AuthSignatureHandler
{
    use XPathTrait;

    protected function prepareH00XXPath(DOMDocument $request): DOMXPath
    {
        return $this->prepareH004XPath($request);
    }
}

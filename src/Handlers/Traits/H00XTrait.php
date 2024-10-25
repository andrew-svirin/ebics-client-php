<?php

namespace AndrewSvirin\Ebics\Handlers\Traits;

use DOMDocument;
use DOMXPath;

/**
 * Trait H00XTrait settings.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
trait H00XTrait
{
    use XPathTrait;

    abstract protected function getH00XVersion(): string;

    abstract protected function getH00XNamespace(): string;

    /**
     * Setup H00X XPath for DOM XML.
     *
     * @param DOMDocument $xml
     *
     * @return DOMXPath
     */
    protected function prepareH00XXPath(DOMDocument $xml): DOMXPath
    {
        $xpath = $this->prepareXPath($xml);
        $xpath->registerNamespace($this->getH00XVersion(), $this->getH00XNamespace());
        $xpath->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');

        return $xpath;
    }
}

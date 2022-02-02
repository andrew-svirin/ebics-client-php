<?php

namespace AndrewSvirin\Ebics\Handlers\Traits;

use DOMDocument;
use DOMNode;
use DOMXPath;

/**
 * Class XPathTrait manage XPath building.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
trait XPathTrait
{

    /**
     * Setup XPath for DOM XML.
     *
     * @param DOMDocument $xml
     *
     * @return DOMXPath
     */
    protected function prepareXPath(DOMDocument $xml): DOMXPath
    {
        $xpath = new DOMXpath($xml);

        return $xpath;
    }

    /**
     * Setup H004 XPath for DOM XML.
     *
     * @param DOMDocument $xml
     *
     * @return DOMXPath
     */
    protected function prepareH004XPath(DOMDocument $xml): DOMXPath
    {
        $xpath = $this->prepareXPath($xml);
        $xpath->registerNamespace('H004', 'urn:org:ebics:H004');
        $xpath->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');

        return $xpath;
    }

    /**
     * Setup H005 XPath for DOM XML.
     *
     * @param DOMDocument $xml
     *
     * @return DOMXPath
     */
    protected function prepareH005XPath(DOMDocument $xml): DOMXPath
    {
        $xpath = $this->prepareXPath($xml);
        $xpath->registerNamespace('H005', 'urn:org:ebics:H005');
        $xpath->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');

        return $xpath;
    }

    /**
     * Setup H000 XPath for DOM XML.
     *
     * @param DOMDocument $xml
     *
     * @return DOMXPath
     */
    protected function prepareH000XPath(DOMDocument $xml): DOMXPath
    {
        $xpath = $this->prepareXPath($xml);
        $xpath->registerNamespace('H000', 'http://www.ebics.org/H000');

        return $xpath;
    }

    /**
     * Setup S001 XPath for DOM XML.
     *
     * @param DOMDocument $xml
     *
     * @return DOMXPath
     */
    protected function prepareS001XPath(DOMDocument $xml): DOMXPath
    {
        $xpath = $this->prepareXPath($xml);
        $xpath->registerNamespace('S001', 'http://www.ebics.org/S001');

        return $xpath;
    }

    /**
     * Insert to DOMDocument after node.
     *
     * @param DOMNode $newNode
     * @param DOMNode $afterNode
     */
    protected function insertAfter(DOMNode $newNode, DOMNode $afterNode): void
    {
        $nextSibling = $afterNode->nextSibling;
        if ($newNode !== $nextSibling) {
            $afterNode->parentNode->insertBefore($newNode, $nextSibling);
        } else {
            $afterNode->parentNode->appendChild($newNode);
        }
    }
}

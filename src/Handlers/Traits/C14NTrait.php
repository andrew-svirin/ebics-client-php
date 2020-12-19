<?php

namespace AndrewSvirin\Ebics\Handlers\Traits;

use AndrewSvirin\Ebics\Exceptions\EbicsException;
use DOMNode;
use DOMXPath;

/**
 * Class C14NTrait manage c14n building.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
trait C14NTrait
{
    /**
     * Extract C14N content by path from the XML DOM.
     *
     * @return string
     *
     * @throws EbicsException
     */
    private function calculateC14N(DOMXPath $xpath, string $path = '/', string $algo = 'REC-xml-c14n-20010315'): string
    {
        switch ($algo) {
            case 'REC-xml-c14n-20010315':
                $exclusive = false;
                $withComments = false;
                break;
            default:
                throw new EbicsException(sprintf('Define algo for %s', $algo));
        }
        $nodes = $xpath->query($path);
        $result = '';

        if (!($nodes instanceof \DOMNodeList)) {
            return $result;
        }

        /* @var $node DOMNode */
        foreach ($nodes as $node) {
            $result .= $node->C14N($exclusive, $withComments);
        }

        return trim($result);
    }
}

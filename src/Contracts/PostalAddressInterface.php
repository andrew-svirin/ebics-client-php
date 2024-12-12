<?php

namespace EbicsApi\Ebics\Contracts;

use DOMDocument;
use DOMElement;

/**
 * PostalAddressInterface
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Jonathan Luthi
 */
interface PostalAddressInterface
{
    /**
     * Returns an XML representation of the address
     *
     * @param DOMDocument $doc
     *
     * @return DOMElement The built DOM element
     */
    public function toDomElement(DOMDocument $doc): DOMElement;
}

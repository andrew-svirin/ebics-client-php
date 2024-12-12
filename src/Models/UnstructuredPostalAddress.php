<?php

namespace EbicsApi\Ebics\Models;

use EbicsApi\Ebics\Contracts\PostalAddressInterface;
use DOMElement;

/**
 * This class holds an unstructured representation of a postal address
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Jonathan Luthi
 */
final class UnstructuredPostalAddress implements PostalAddressInterface
{
    protected string $country;
    protected array $addressLines;

    /**
     * Constructor
     *
     * @param string $country Country code (ISO 3166-1 alpha-2) (max 2 char)
     * @param string|null $addressLine1 Street name and house number (max 70 char)
     * @param string|null $addressLine2 Postcode and town (max 70 char)
     */
    public function __construct(
        string $country,
        ?string $addressLine1,
        ?string $addressLine2 = null
    ) {
        $this->country = $country;
        $this->addressLines = [];
        if ($addressLine1 !== null) {
            $this->addressLines[] = $addressLine1;
        }
        if ($addressLine2 !== null) {
            $this->addressLines[] = $addressLine2;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function toDomElement(\DOMDocument $doc): DOMElement
    {
        $xmlPstlAdr = $doc->createElement('PstlAdr');

        $xmlCtry = $doc->createElement('Ctry');
        $xmlCtry->nodeValue = $this->country;
        $xmlPstlAdr->appendChild($xmlCtry);

        foreach ($this->addressLines as $line) {
            $xmlAdrLine = $doc->createElement('AdrLine');
            $xmlAdrLine->nodeValue = $line;
            $xmlPstlAdr->appendChild($xmlAdrLine);
        }

        return $xmlPstlAdr;
    }
}

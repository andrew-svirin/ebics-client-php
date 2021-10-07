<?php

namespace AndrewSvirin\Ebics\Models;

use AndrewSvirin\Ebics\Contracts\PostalAddressInterface;
use DOMElement;

/**
 * This class holds an unstructured representation of a postal address
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Jonathan Luthi
 */
class UnstructuredPostalAddress implements PostalAddressInterface
{
    /**
     * @var array
     */
    protected $addressLines;

    /**
     * @var string
     */
    protected $country;

    /**
     * Constructor
     *
     * @param string|null $addressLine1 Street name and house number
     * @param string|null $addressLine2 Postcode and town
     * @param string $country      Country code (ISO 3166-1 alpha-2)
     */
    public function __construct(
        ?string $addressLine1,
        ?string $addressLine2 = null,
        string $country = 'CH'
    ) {
        $this->addressLines = [];
        if ($addressLine1 !== null) {
            $this->addressLines[] = substr(trim($addressLine1), 0, 70);
        }
        if ($addressLine2 !== null) {
            $this->addressLines[] = substr(trim($addressLine2), 0, 70);
        }
        $this->country = strtoupper(substr(trim($country), 0, 2));
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

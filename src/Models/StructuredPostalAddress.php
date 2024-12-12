<?php

namespace EbicsApi\Ebics\Models;

use EbicsApi\Ebics\Contracts\PostalAddressInterface;
use DOMElement;

/**
 * This class holds a structured representation of a postal address
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Jonathan Luthi
 */
final class StructuredPostalAddress implements PostalAddressInterface
{
    protected string $country;
    protected string $town;
    protected string $postCode;
    protected ?string $street;
    protected ?string $buildingNo;

    /**
     * Constructor
     *
     * @param string $country Country code (ISO 3166-1 alpha-2) (max 2 char)
     * @param string $town Town name (max 35 char)
     * @param string $postCode Postal code (max 16 char)
     * @param string|null $street Street name or null (max 70 char)
     * @param string|null $buildingNo Building number or null (max 16 char)
     */
    public function __construct(
        string $country,
        string $town,
        string $postCode,
        ?string $street = null,
        ?string $buildingNo = null
    ) {
        $this->country = $country;
        $this->town = $town;
        $this->postCode = $postCode;
        $this->street = $street;
        $this->buildingNo = $buildingNo;
    }

    /**
     * {@inheritdoc}
     */
    public function toDomElement(\DOMDocument $doc): DOMElement
    {
        $xmlPstlAdr = $doc->createElement('PstlAdr');

        if ($this->street !== null) {
            $xmlStrtNm = $doc->createElement('StrtNm');
            $xmlStrtNm->nodeValue = $this->street;
            $xmlPstlAdr->appendChild($xmlStrtNm);
        }

        if ($this->buildingNo !== null) {
            $xmlBldgNb = $doc->createElement('BldgNb');
            $xmlBldgNb->nodeValue = $this->buildingNo;
            $xmlPstlAdr->appendChild($xmlBldgNb);
        }

        $xmlPstCd = $doc->createElement('PstCd');
        $xmlPstCd->nodeValue = $this->postCode;
        $xmlPstlAdr->appendChild($xmlPstCd);

        $xmlTwnNm = $doc->createElement('TwnNm');
        $xmlTwnNm->nodeValue = $this->town;
        $xmlPstlAdr->appendChild($xmlTwnNm);

        $xmlCtry = $doc->createElement('Ctry');
        $xmlCtry->nodeValue = $this->country;
        $xmlPstlAdr->appendChild($xmlCtry);

        return $xmlPstlAdr;
    }
}

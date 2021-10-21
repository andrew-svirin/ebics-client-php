<?php

namespace AndrewSvirin\Ebics\Models;

use AndrewSvirin\Ebics\Contracts\PostalAddressInterface;
use DOMElement;

/**
 * This class holds a structured representation of a postal address
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Jonathan Luthi
 */
class StructuredPostalAddress implements PostalAddressInterface
{
    /**
     * @var string|null
     */
    protected $street;

    /**
     * @var string|null
     */
    protected $buildingNo;

    /**
     * @var string
     */
    protected $postCode;

    /**
     * @var string
     */
    protected $town;

    /**
     * @var string
     */
    protected $country;

    /**
     * Constructor
     *
     * @param string|null $street Street name or null
     * @param string|null $buildingNo Building number or null
     * @param string $postCode Postal code
     * @param string $town Town name
     * @param string $country Country code (ISO 3166-1 alpha-2)
     */
    public function __construct(
        ?string $street,
        ?string $buildingNo,
        string $postCode,
        string $town,
        string $country = 'CH'
    ) {
        $this->street = $street !== null && trim($street) !== '' ? substr(trim($street), 0, 70) : null;
        $this->buildingNo = $buildingNo !== null && trim($buildingNo) !== '' ? substr(trim($buildingNo), 0, 16) : null;
        $this->postCode = substr(trim($postCode), 0, 16);
        $this->town = substr(trim($town), 0, 35);
        $this->country = strtoupper(substr(trim($country), 0, 2));
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

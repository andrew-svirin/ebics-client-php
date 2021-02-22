<?php

namespace AndrewSvirin\Ebics\Builders;

use DateTimeInterface;
use DOMDocument;
use DOMElement;

/**
 * Class OrderDetailsBuilder builder for request container.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class OrderDetailsBuilder
{

    const ORDER_ATTRIBUTE_DZNNN = 'DZNNN';
    const ORDER_ATTRIBUTE_DZHNN = 'DZHNN';
    const ORDER_ATTRIBUTE_OZHNN = 'OZHNN';

    /**
     * @var DOMElement
     */
    private $instance;

    /**
     * @var DOMDocument
     */
    private $dom;

    public function __construct(DOMDocument $dom = null)
    {
        $this->dom = $dom;
    }

    /**
     * Create body for UnsecuredRequest.
     *
     * @return $this
     */
    public function createInstance(): OrderDetailsBuilder
    {
        $this->instance = $this->dom->createElement('OrderDetails');

        return $this;
    }

    public function addOrderType(string $orderType): OrderDetailsBuilder
    {
        $xmlOrderType = $this->dom->createElement('OrderType');
        $xmlOrderType->nodeValue = $orderType;
        $this->instance->appendChild($xmlOrderType);

        return $this;
    }

    public function addOrderAttribute(string $orderAttribute): OrderDetailsBuilder
    {
        $xmlOrderAttribute = $this->dom->createElement('OrderAttribute');
        $xmlOrderAttribute->nodeValue = $orderAttribute;
        $this->instance->appendChild($xmlOrderAttribute);

        return $this;
    }

    public function addStandardOrderParams(
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null
    ): OrderDetailsBuilder {
        $xmlStandardOrderParams = $this->dom->createElement('StandardOrderParams');
        $this->instance->appendChild($xmlStandardOrderParams);

        if (null !== $startDateTime && null !== $endDateTime) {
            // Add DateRange to StandardOrderParams.
            $xmlDateRange = $this->createDateRange($startDateTime, $endDateTime);
            $xmlStandardOrderParams->appendChild($xmlDateRange);
        }

        return $this;
    }

    public function addFDLOrderParams(
        string $fileFormat,
        string $countryCode,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null
    ): OrderDetailsBuilder {
        // Add StandardOrderParams to OrderDetails.
        $xmlFDLOrderParams = $this->dom->createElement('FDLOrderParams');
        $this->instance->appendChild($xmlFDLOrderParams);

        // Add FileFormat to FDLOrderParams.
        $xmlFileFormat = $this->dom->createElement('FileFormat');
        $xmlFileFormat->nodeValue = $fileFormat;
        $xmlFileFormat->setAttribute('CountryCode', $countryCode);
        $xmlFDLOrderParams->appendChild($xmlFileFormat);

        if (null !== $startDateTime && null !== $endDateTime) {
            // Add DateRange to FDLOrderParams.
            $xmlDateRange = $this->createDateRange($startDateTime, $endDateTime);
            $xmlFDLOrderParams->appendChild($xmlDateRange);
        }

        return $this;
    }

    private function createDateRange(DateTimeInterface $startDateTime, DateTimeInterface $endDateTime): DOMElement
    {
        $xmlDateRange = $this->dom->createElement('DateRange');

        // Add Start to StandardOrderParams.
        $xmlStart = $this->dom->createElement('Start');
        $xmlStart->nodeValue = $startDateTime->format('Y-m-d');
        $xmlDateRange->appendChild($xmlStart);

        // Add End to StandardOrderParams.
        $xmlEnd = $this->dom->createElement('End');
        $xmlEnd->nodeValue = $endDateTime->format('Y-m-d');
        $xmlDateRange->appendChild($xmlEnd);

        return $xmlDateRange;
    }

    public function getInstance(): DOMElement
    {
        return $this->instance;
    }
}

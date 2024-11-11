<?php

namespace AndrewSvirin\Ebics\Builders\Request;

use AndrewSvirin\Ebics\Contexts\BTDContext;
use AndrewSvirin\Ebics\Contexts\BTUContext;
use AndrewSvirin\Ebics\Contexts\FDLContext;
use AndrewSvirin\Ebics\Contexts\FULContext;
use AndrewSvirin\Ebics\Contexts\HVDContext;
use AndrewSvirin\Ebics\Contexts\HVEContext;
use AndrewSvirin\Ebics\Contexts\HVTContext;
use DateTimeInterface;
use DOMDocument;
use DOMElement;

/**
 * Abstract Class OrderDetailsBuilder builder for request container.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
abstract class OrderDetailsBuilder
{
    const ORDER_ATTRIBUTE_DZNNN = 'DZNNN';
    const ORDER_ATTRIBUTE_DZHNN = 'DZHNN';
    const ORDER_ATTRIBUTE_UZHNN = 'UZHNN';
    const ORDER_ATTRIBUTE_OZHNN = 'OZHNN';

    protected DOMElement $instance;
    protected ?DOMDocument $dom;

    public function __construct(?DOMDocument $dom = null)
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

    abstract public function addOrderType(string $orderType): OrderDetailsBuilder;

    abstract public function addAdminOrderType(string $orderType): OrderDetailsBuilder;

    public function addOrderId(string $orderId): OrderDetailsBuilder
    {
        $xmlOrderID = $this->dom->createElement('OrderID');
        $xmlOrderID->nodeValue = $orderId;
        $this->instance->appendChild($xmlOrderID);

        return $this;
    }

    abstract public function addOrderAttribute(string $orderAttribute): OrderDetailsBuilder;

    public function addStandardOrderParams(
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null
    ): OrderDetailsBuilder {
        // Add StandardOrderParams to OrderDetails.
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
        FDLContext $fdlContext,
        ?DateTimeInterface $startDateTime,
        ?DateTimeInterface $endDateTime
    ): OrderDetailsBuilder {
        // Add FDLOrderParams to OrderDetails.
        $xmlFDLOrderParams = $this->dom->createElement('FDLOrderParams');
        $this->instance->appendChild($xmlFDLOrderParams);

        if (null !== $startDateTime && null !== $endDateTime) {
            // Add DateRange to FDLOrderParams.
            $xmlDateRange = $this->createDateRange($startDateTime, $endDateTime);
            $xmlFDLOrderParams->appendChild($xmlDateRange);
        }

        $this->addParameters($xmlFDLOrderParams, $fdlContext->getParameters());

        // Add FileFormat to FDLOrderParams.
        $xmlFileFormat = $this->dom->createElement('FileFormat');
        $xmlFileFormat->nodeValue = $fdlContext->getFileFormat();
        $xmlFDLOrderParams->appendChild($xmlFileFormat);

        $xmlFileFormat->setAttribute('CountryCode', $fdlContext->getCountryCode());

        return $this;
    }

    public function addFULOrderParams(FULContext $fulContext): OrderDetailsBuilder
    {
        // Add FULOrderParams to OrderDetails.
        $xmlFULOrderParams = $this->dom->createElement('FULOrderParams');
        $this->instance->appendChild($xmlFULOrderParams);

        $this->addParameters($xmlFULOrderParams, $fulContext->getParameters());

        // Add FileFormat to FULOrderParams.
        $xmlFileFormat = $this->dom->createElement('FileFormat');
        $xmlFileFormat->nodeValue = $fulContext->getFileFormat();
        $xmlFULOrderParams->appendChild($xmlFileFormat);

        $xmlFileFormat->setAttribute('CountryCode', $fulContext->getCountryCode());

        return $this;
    }

    private function addParameters(DOMElement $orderParams, array $parameters): void
    {
        foreach ($parameters as $name => $value) {
            // Add Parameter to FULOrderParams.
            $xmlParameter = $this->dom->createElement('Parameter');
            $orderParams->appendChild($xmlParameter);

            // Add Name to Parameter.
            $xmlName = $this->dom->createElement('Name');
            $xmlParameter->appendChild($xmlName);
            $xmlName->nodeValue = $name;

            // Add Value to Parameter.
            $xmlValue = $this->dom->createElement('Value');
            $xmlParameter->appendChild($xmlValue);
            $xmlValue->nodeValue = $value;

            $xmlValue->setAttribute('Type', 'string');
        }
    }

    public function getInstance(): DOMElement
    {
        return $this->instance;
    }

    abstract public function addHVEOrderParams(HVEContext $hveContext): OrderDetailsBuilder;

    public function addHVUOrderParams(): OrderDetailsBuilder
    {
        // Add HVUOrderParams to OrderDetails.
        $xmlHVUOrderParams = $this->dom->createElement('HVUOrderParams');
        $this->instance->appendChild($xmlHVUOrderParams);

        return $this;
    }

    public function addHVZOrderParams(): OrderDetailsBuilder
    {
        // Add HVZOrderParams to OrderDetails.
        $xmlHVZOrderParams = $this->dom->createElement('HVZOrderParams');
        $this->instance->appendChild($xmlHVZOrderParams);

        return $this;
    }

    abstract public function addHVDOrderParams(HVDContext $hvdContext): OrderDetailsBuilder;

    abstract public function addHVTOrderParams(HVTContext $hvtContext): OrderDetailsBuilder;

    abstract public function addBTDOrderParams(
        BTDContext $btfContext,
        ?DateTimeInterface $startDateTime = null,
        ?DateTimeInterface $endDateTime = null
    ): OrderDetailsBuilder;

    abstract public function addBTUOrderParams(BTUContext $btuContext): OrderDetailsBuilder;

    protected function createDateRange(DateTimeInterface $startDateTime, DateTimeInterface $endDateTime): DOMElement
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
}

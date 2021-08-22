<?php

namespace AndrewSvirin\Ebics\Builders\Request;

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
    const ORDER_ATTRIBUTE_UZHNN = 'UZHNN';
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

    /**
     * Since EBICS 3.0 the AdminOrderType is mandatory inside the OrderDetails element.
     * For EBICS 2.x it is ignored and the OrderType is used instead.
     * @param string $orderType
     * @return $this
     */
    public function addAdminOrderType(string $orderType): OrderDetailsBuilder
    {
        $xmlOrderType = $this->dom->createElement('AdminOrderType');
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

    public function addBTDOrderParams(
        string $serviceName,
        string $scope,
        string $msgName,
        ?DateTimeInterface $startDateTime = null,
        ?DateTimeInterface $endDateTime = null
    ): OrderDetailsBuilder {
        // Add BTDOrderParams to OrderDetails.
        $xmlBTDOrderParams = $this->dom->createElement('BTDOrderParams');
        $this->instance->appendChild($xmlBTDOrderParams);

        // Add Service to BTDOrderParams.
        $xmlService = $this->dom->createElement('Service');
        $xmlBTDOrderParams->appendChild($xmlService);

        // Add ServiceName to Service.
        $xmlServiceName = $this->dom->createElement('ServiceName');
        $xmlServiceName->nodeValue = $serviceName;
        $xmlService->appendChild($xmlServiceName);

        // Add Scope to Service.
        $xmlScope = $this->dom->createElement('Scope');
        $xmlScope->nodeValue = $scope;
        $xmlService->appendChild($xmlScope);

        // Add MsgName to Service.
        $xmlMsgName = $this->dom->createElement('MsgName');
        $xmlMsgName->nodeValue = $msgName;
        $xmlService->appendChild($xmlMsgName);

        if (null !== $startDateTime && null !== $endDateTime) {
            // Add DateRange to BTDOrderParams.
            $xmlDateRange = $this->createDateRange($startDateTime, $endDateTime);
            $xmlBTDOrderParams->appendChild($xmlDateRange);
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

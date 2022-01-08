<?php

namespace AndrewSvirin\Ebics\Builders\Request;

use AndrewSvirin\Ebics\Contexts\BTFContext;
use DateTimeInterface;
use DOMDocument;
use DOMElement;

/**
 * Class OrderDetailsBuilder builder for request container.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class OrderDetailsBuilder
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
        BTFContext $btfContext,
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
        $xmlServiceName->nodeValue = $btfContext->getServiceName();
        $xmlService->appendChild($xmlServiceName);

        // Add optional ServiceOption to Service.
        if (null !== $btfContext->getServiceOption()) {
            $xmlServiceOption = $this->dom->createElement('ServiceOption');
            $xmlServiceOption->nodeValue = $btfContext->getServiceOption();
            $xmlService->appendChild($xmlServiceOption);
        }

        // Add optional ContainerFlag to Service.
        if (null !== $btfContext->getContainerFlag()) {
            $xmlContainerFlag = $this->dom->createElement('ContainerFlag');
            $xmlContainerFlag->nodeValue = $btfContext->getContainerFlag();
            $xmlService->appendChild($xmlContainerFlag);
        }

        // Add optional Scope to Service.
        if (null !== $btfContext->getScope()) {
            $xmlScope = $this->dom->createElement('Scope');
            $xmlScope->nodeValue = $btfContext->getScope();
            $xmlService->appendChild($xmlScope);
        }

        // Add MsgName to Service.
        $xmlMsgName = $this->dom->createElement('MsgName');
        $xmlMsgName->nodeValue = $btfContext->getMsgName();
        $xmlService->appendChild($xmlMsgName);

        // Add optional MsgName version attribute
        if (null !== $btfContext->getMsgNameVersion()) {
            $xmlMsgName->setAttribute('version', $btfContext->getMsgNameVersion());
        }

        // Add optional MsgName variant attribute
        if (null !== $btfContext->getMsgNameVariant()) {
            $xmlMsgName->setAttribute('variant', $btfContext->getMsgNameVariant());
        }

        // Add optional MsgName format attribute
        if (null !== $btfContext->getMsgNameFormat()) {
            $xmlMsgName->setAttribute('format', $btfContext->getMsgNameFormat());
        }

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

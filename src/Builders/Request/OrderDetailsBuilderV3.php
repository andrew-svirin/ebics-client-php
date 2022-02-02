<?php

namespace AndrewSvirin\Ebics\Builders\Request;

use AndrewSvirin\Ebics\Contexts\BTFContext;
use AndrewSvirin\Ebics\Contexts\BTUContext;
use AndrewSvirin\Ebics\Contexts\HVDContext;
use AndrewSvirin\Ebics\Contexts\HVEContext;
use AndrewSvirin\Ebics\Contexts\HVTContext;
use DateTimeInterface;
use LogicException;

/**
 * Ebics 3.0 Class OrderDetailsBuilder builder for request container.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class OrderDetailsBuilderV3 extends OrderDetailsBuilder
{
    public function addOrderType(string $orderType): OrderDetailsBuilder
    {
        throw new LogicException('Unsupported yet');
    }

    public function addAdminOrderType(string $orderType): OrderDetailsBuilder
    {
        $xmlOrderType = $this->dom->createElement('AdminOrderType');
        $xmlOrderType->nodeValue = $orderType;
        $this->instance->appendChild($xmlOrderType);

        return $this;
    }

    public function addOrderAttribute(string $orderAttribute): OrderDetailsBuilder
    {
        throw new LogicException('Unsupported yet');
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

    public function addBTUOrderParams(BTUContext $btfContext): OrderDetailsBuilder
    {
        // Add BTDOrderParams to OrderDetails.
        $xmlBTDOrderParams = $this->dom->createElement('BTUOrderParams');
        $xmlBTDOrderParams->setAttribute('fileName', 'CCT.xml');
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

        return $this;
    }

    public function addHVEOrderParams(HVEContext $hveContext): OrderDetailsBuilder
    {
        // Add HVEOrderParams to OrderDetails.
        $xmlHVEOrderParams = $this->dom->createElement('HVEOrderParams');
        $this->instance->appendChild($xmlHVEOrderParams);

        $xmlPartnerID = $this->dom->createElement('PartnerID');
        $xmlPartnerID->nodeValue = $hveContext->getPartnerId();
        $xmlHVEOrderParams->appendChild($xmlPartnerID);

        // Add Service to HVEOrderParams.
        $xmlService = $this->dom->createElement('Service');
        $xmlHVEOrderParams->appendChild($xmlService);

        // Add ServiceName to Service.
        $xmlServiceName = $this->dom->createElement('ServiceName');
        $xmlServiceName->nodeValue = $hveContext->getServiceName();
        $xmlService->appendChild($xmlServiceName);

        // Add optional Scope to Service.
        if (null !== $hveContext->getScope()) {
            $xmlScope = $this->dom->createElement('Scope');
            $xmlScope->nodeValue = $hveContext->getScope();
            $xmlService->appendChild($xmlScope);
        }

        // Add optional ServiceOption to Service.
        if (null !== $hveContext->getServiceOption()) {
            $xmlServiceOption = $this->dom->createElement('ServiceOption');
            $xmlServiceOption->nodeValue = $hveContext->getServiceOption();
            $xmlService->appendChild($xmlServiceOption);
        }

        // Add MsgName to Service.
        $xmlMsgName = $this->dom->createElement('MsgName');
        $xmlMsgName->nodeValue = $hveContext->getMsgName();
        $xmlService->appendChild($xmlMsgName);

        // Add OrderID to HVEOrderParams.
        $xmlOrderID = $this->dom->createElement('OrderID');
        $xmlOrderID->nodeValue = $hveContext->getOrderId();
        $xmlHVEOrderParams->appendChild($xmlOrderID);

        return $this;
    }

    public function addHVDOrderParams(HVDContext $hvdContext): OrderDetailsBuilder
    {
        // Add HVDOrderParams to OrderDetails.
        $xmlHVDOrderParams = $this->dom->createElement('HVDOrderParams');
        $this->instance->appendChild($xmlHVDOrderParams);

        $xmlPartnerID = $this->dom->createElement('PartnerID');
        $xmlPartnerID->nodeValue = $hvdContext->getPartnerId();
        $xmlHVDOrderParams->appendChild($xmlPartnerID);

        // Add Service to HVDOrderParams.
        $xmlService = $this->dom->createElement('Service');
        $xmlHVDOrderParams->appendChild($xmlService);

        // Add ServiceName to Service.
        $xmlServiceName = $this->dom->createElement('ServiceName');
        $xmlServiceName->nodeValue = $hvdContext->getServiceName();
        $xmlService->appendChild($xmlServiceName);

        // Add optional Scope to Service.
        if (null !== $hvdContext->getScope()) {
            $xmlScope = $this->dom->createElement('Scope');
            $xmlScope->nodeValue = $hvdContext->getScope();
            $xmlService->appendChild($xmlScope);
        }

        // Add optional ServiceOption to Service.
        if (null !== $hvdContext->getServiceOption()) {
            $xmlServiceOption = $this->dom->createElement('ServiceOption');
            $xmlServiceOption->nodeValue = $hvdContext->getServiceOption();
            $xmlService->appendChild($xmlServiceOption);
        }

        // Add MsgName to Service.
        $xmlMsgName = $this->dom->createElement('MsgName');
        $xmlMsgName->nodeValue = $hvdContext->getMsgName();
        $xmlService->appendChild($xmlMsgName);

        // Add OrderID to HVDOrderParams.
        $xmlOrderID = $this->dom->createElement('OrderID');
        $xmlOrderID->nodeValue = $hvdContext->getOrderId();
        $xmlHVDOrderParams->appendChild($xmlOrderID);

        return $this;
    }

    public function addHVTOrderParams(HVTContext $hvtContext): OrderDetailsBuilder
    {
        // Add HVTOrderParams to OrderDetails.
        $xmlHVTOrderParams = $this->dom->createElement('HVTOrderParams');
        $this->instance->appendChild($xmlHVTOrderParams);

        $xmlPartnerID = $this->dom->createElement('PartnerID');
        $xmlPartnerID->nodeValue = $hvtContext->getPartnerId();
        $xmlHVTOrderParams->appendChild($xmlPartnerID);

        // Add Service to HVDOrderParams.
        $xmlService = $this->dom->createElement('Service');
        $xmlHVTOrderParams->appendChild($xmlService);

        // Add ServiceName to Service.
        $xmlServiceName = $this->dom->createElement('ServiceName');
        $xmlServiceName->nodeValue = $hvtContext->getServiceName();
        $xmlService->appendChild($xmlServiceName);

        // Add optional Scope to Service.
        if (null !== $hvtContext->getScope()) {
            $xmlScope = $this->dom->createElement('Scope');
            $xmlScope->nodeValue = $hvtContext->getScope();
            $xmlService->appendChild($xmlScope);
        }

        // Add optional ServiceOption to Service.
        if (null !== $hvtContext->getServiceOption()) {
            $xmlServiceOption = $this->dom->createElement('ServiceOption');
            $xmlServiceOption->nodeValue = $hvtContext->getServiceOption();
            $xmlService->appendChild($xmlServiceOption);
        }

        // Add MsgName to Service.
        $xmlMsgName = $this->dom->createElement('MsgName');
        $xmlMsgName->nodeValue = $hvtContext->getMsgName();
        $xmlService->appendChild($xmlMsgName);

        // Add OrderID to HVTOrderParams.
        $xmlOrderID = $this->dom->createElement('OrderID');
        $xmlOrderID->nodeValue = $hvtContext->getOrderId();
        $xmlHVTOrderParams->appendChild($xmlOrderID);

        // Add OrderFlags to HVTOrderParams.
        $xmlOrderFlags = $this->dom->createElement('OrderFlags');
        $xmlOrderFlags->setAttribute('completeOrderData', $hvtContext->getCompleteOrderData() ? 'true' : 'false');
        $xmlOrderFlags->setAttribute('fetchLimit', (string)$hvtContext->getFetchLimit());
        $xmlOrderFlags->setAttribute('fetchOffset', (string)$hvtContext->getFetchOffset());
        $xmlHVTOrderParams->appendChild($xmlOrderFlags);

        return $this;
    }
}

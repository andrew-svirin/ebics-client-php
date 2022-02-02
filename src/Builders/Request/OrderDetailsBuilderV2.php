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
 * Ebics 2.5 Class OrderDetailsBuilder builder for request container.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class OrderDetailsBuilderV2 extends OrderDetailsBuilder
{
    public function addOrderType(string $orderType): OrderDetailsBuilder
    {
        $xmlOrderType = $this->dom->createElement('OrderType');
        $xmlOrderType->nodeValue = $orderType;
        $this->instance->appendChild($xmlOrderType);

        return $this;
    }

    public function addAdminOrderType(string $orderType): OrderDetailsBuilder
    {
        throw new LogicException('Unsupported yet');
    }

    public function addOrderAttribute(string $orderAttribute): OrderDetailsBuilder
    {
        $xmlOrderAttribute = $this->dom->createElement('OrderAttribute');
        $xmlOrderAttribute->nodeValue = $orderAttribute;
        $this->instance->appendChild($xmlOrderAttribute);

        return $this;
    }

    public function addBTDOrderParams(
        BTFContext $btfContext,
        ?DateTimeInterface $startDateTime = null,
        ?DateTimeInterface $endDateTime = null
    ): OrderDetailsBuilder {
        throw new LogicException('Unsupported yet');
    }

    public function addBTUOrderParams(BTUContext $btuContext): OrderDetailsBuilder
    {
        throw new LogicException('Unsupported yet');
    }

    public function addHVEOrderParams(HVEContext $hveContext): OrderDetailsBuilder
    {
        // Add HVEOrderParams to OrderDetails.
        $xmlHVEOrderParams = $this->dom->createElement('HVEOrderParams');
        $this->instance->appendChild($xmlHVEOrderParams);

        $xmlPartnerID = $this->dom->createElement('PartnerID');
        $xmlPartnerID->nodeValue = $hveContext->getPartnerId();
        $xmlHVEOrderParams->appendChild($xmlPartnerID);

        $xmlOrderType = $this->dom->createElement('OrderType');
        $xmlOrderType->nodeValue = $hveContext->getOrderType();
        $xmlHVEOrderParams->appendChild($xmlOrderType);

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

        $xmlOrderType = $this->dom->createElement('OrderType');
        $xmlOrderType->nodeValue = $hvdContext->getOrderType();
        $xmlHVDOrderParams->appendChild($xmlOrderType);

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

        $xmlOrderType = $this->dom->createElement('OrderType');
        $xmlOrderType->nodeValue = $hvtContext->getOrderType();
        $xmlHVTOrderParams->appendChild($xmlOrderType);


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

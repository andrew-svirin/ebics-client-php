<?php

namespace AndrewSvirin\Ebics\Handlers;

use AndrewSvirin\Ebics\Handlers\Traits\XPathTrait;
use AndrewSvirin\Ebics\Services\DOMHelper;
use DOMDocument;

/**
 * Ebics 2.5 ResponseHandler.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class ResponseHandlerV2 extends ResponseHandler
{
    use XPathTrait;

    public function retrieveH00XReturnCode(DOMDocument $xml): string
    {
        $xpath = $this->prepareH004XPath($xml);
        $value = $xpath->query('//H004:header/H004:mutable/H004:ReturnCode');

        return DOMHelper::safeItemValue($value);
    }

    public function retrieveH00XBodyReturnCode(DOMDocument $xml): string
    {
        $xpath = $this->prepareH004XPath($xml);
        $value = $xpath->query('//H004:body/H004:ReturnCode');

        return DOMHelper::safeItemValue($value);
    }

    public function retrieveH00XReportText(DOMDocument $xml): string
    {
        $xpath = $this->prepareH004XPath($xml);
        $value = $xpath->query('//H004:header/H004:mutable/H004:ReportText');

        return DOMHelper::safeItemValue($value);
    }

    public function retrieveH00XTransactionId(DOMDocument $xml): ?string
    {
        $xpath = $this->prepareH004XPath($xml);
        $value = $xpath->query("//H004:header/H004:static/H004:TransactionID");

        return DOMHelper::safeItemValueOrNull($value);
    }

    public function retrieveH00XTransactionPhase(DOMDocument $xml): ?string
    {
        $xpath = $this->prepareH004XPath($xml);
        $value = $xpath->query("//H004:header/H004:mutable/H004:TransactionPhase");

        return DOMHelper::safeItemValueOrNull($value);
    }

    public function retrieveH00XNumSegments(DOMDocument $xml): ?string
    {
        $xpath = $this->prepareH004XPath($xml);
        $value = $xpath->query("//H004:header/H004:static/H004:NumSegments");

        return DOMHelper::safeItemValueOrNull($value);
    }

    public function retrieveH00XOrderId(DOMDocument $xml): ?string
    {
        $xpath = $this->prepareH004XPath($xml);
        $value = $xpath->query("//H004:header/H004:mutable/H004:OrderID");

        return DOMHelper::safeItemValueOrNull($value);
    }

    public function retrieveH00XSegmentNumber(DOMDocument $xml): ?string
    {
        $xpath = $this->prepareH004XPath($xml);
        $value = $xpath->query("//H004:header/H004:mutable/H004:SegmentNumber");

        return DOMHelper::safeItemValueOrNull($value);
    }

    public function retrieveH00XTransactionKey(DOMDocument $xml): ?string
    {
        $xpath = $this->prepareH004XPath($xml);
        $value = $xpath->query("//H004:body/H004:DataTransfer/H004:DataEncryptionInfo/H004:TransactionKey");

        return DOMHelper::safeItemValueOrNull($value);
    }

    public function retrieveH00XOrderData(DOMDocument $xml): ?string
    {
        $xpath = $this->prepareH004XPath($xml);
        $value = $xpath->query("//H004:body/H004:DataTransfer/H004:OrderData");

        return DOMHelper::safeItemValueOrNull($value);
    }
}

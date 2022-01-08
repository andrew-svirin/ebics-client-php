<?php

namespace AndrewSvirin\Ebics\Handlers;

use AndrewSvirin\Ebics\Handlers\Traits\XPathTrait;
use AndrewSvirin\Ebics\Services\DOMHelper;
use DOMDocument;

/**
 * Ebics 3.0 ResponseHandler.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class ResponseHandlerV3 extends ResponseHandler
{
    use XPathTrait;

    public function retrieveH00XReturnCode(DOMDocument $xml): string
    {
        $xpath = $this->prepareH005XPath($xml);
        $returnCode = $xpath->query('//H005:header/H005:mutable/H005:ReturnCode');

        return DOMHelper::safeItemValue($returnCode);
    }

    public function retrieveH00XBodyReturnCode(DOMDocument $xml): string
    {
        $xpath = $this->prepareH005XPath($xml);
        $returnCode = $xpath->query('//H005:body/H005:ReturnCode');

        return DOMHelper::safeItemValue($returnCode);
    }

    public function retrieveH00XReportText(DOMDocument $xml): string
    {
        $xpath = $this->prepareH005XPath($xml);
        $reportText = $xpath->query('//H005:header/H005:mutable/H005:ReportText');

        return DOMHelper::safeItemValue($reportText);
    }

    public function retrieveH00XTransactionId(DOMDocument $xml): ?string
    {
        $xpath = $this->prepareH005XPath($xml);
        $value = $xpath->query("//H005:header/H005:static/H005:TransactionID");

        return DOMHelper::safeItemValueOrNull($value);
    }

    public function retrieveH00XTransactionPhase(DOMDocument $xml): ?string
    {
        $xpath = $this->prepareH005XPath($xml);
        $value = $xpath->query("//H005:header/H005:mutable/H005:TransactionPhase");

        return DOMHelper::safeItemValueOrNull($value);
    }

    public function retrieveH00XNumSegments(DOMDocument $xml): ?string
    {
        $xpath = $this->prepareH005XPath($xml);
        $value = $xpath->query("//H005:header/H005:static/H005:NumSegments");

        return DOMHelper::safeItemValueOrNull($value);
    }

    public function retrieveH00XOrderId(DOMDocument $xml): ?string
    {
        $xpath = $this->prepareH005XPath($xml);
        $value = $xpath->query("//H005:header/H005:mutable/H005:OrderID");

        return DOMHelper::safeItemValueOrNull($value);
    }

    public function retrieveH00XSegmentNumber(DOMDocument $xml): ?string
    {
        $xpath = $this->prepareH005XPath($xml);
        $value = $xpath->query("//H005:header/H005:mutable/H005:SegmentNumber");

        return DOMHelper::safeItemValueOrNull($value);
    }

    public function retrieveH00XTransactionKey(DOMDocument $xml): ?string
    {
        $xpath = $this->prepareH005XPath($xml);
        $value = $xpath->query("//H005:body/H005:DataTransfer/H005:DataEncryptionInfo/H005:TransactionKey");

        return DOMHelper::safeItemValueOrNull($value);
    }

    public function retrieveH00XOrderData(DOMDocument $xml): ?string
    {
        $xpath = $this->prepareH005XPath($xml);
        $value = $xpath->query("//H005:body/H005:DataTransfer/H005:OrderData");

        return DOMHelper::safeItemValueOrNull($value);
    }
}

<?php

namespace AndrewSvirin\Ebics\Handlers;

use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Factories\TransactionFactory;
use AndrewSvirin\Ebics\Handlers\Traits\XPathTrait;
use AndrewSvirin\Ebics\Models\OrderDataEncrypted;
use AndrewSvirin\Ebics\Models\Transaction;
use AndrewSvirin\Ebics\Services\SafeItems;
use DOMDocument;
use function Safe\base64_decode;

/**
 * Class ResponseHandler manage response DOM elements.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class ResponseHandler
{
    use XPathTrait;

    /**
     * Extract H004 > KeyManagementResponse > header > mutable > ReturnCode value from the DOM XML.
     */
    public function retrieveH004ReturnCode(DOMDocument $xml): string
    {
        $xpath = $this->prepareH004XPath($xml);
        $returnCode = $xpath->query('//H004:header/H004:mutable/H004:ReturnCode');

        return SafeItems::safeItemAcces($returnCode);
    }

    /**
     * Extract H004 > KeyManagementResponse > body > ReturnCode value from the DOM XML.
     */
    public function retrieveH004BodyReturnCode(DOMDocument $xml): string
    {
        $xpath = $this->prepareH004XPath($xml);
        $returnCode = $xpath->query('//H004:body/H004:ReturnCode');

        return SafeItems::safeItemAcces($returnCode);
    }

    /**
     * Extract H004 > ReturnCode value from both header and body. Sometimes (FrenchBank) header code is 00000 whereas body return isn't...
     */
    public function retrieveH004BodyOrHeaderReturnCode(DOMDocument $xml): string
    {
        $headerReturnCode = $this->retrieveH004ReturnCode($xml);
        $bodyReturnCode = $this->retrieveH004BodyReturnCode($xml);

        if ('000000' !== $headerReturnCode) {
            return $headerReturnCode;
        }

        return $bodyReturnCode;
    }

    /**
     * Extract H004 > KeyManagementResponse > header > mutable > ReportText value from the DOM XML.
     */
    public function retrieveH004ReportText(DOMDocument $xml): string
    {
        $xpath = $this->prepareH004XPath($xml);
        $reportText = $xpath->query('//H004:header/H004:mutable/H004:ReportText');

        return SafeItems::safeItemAcces($reportText);
    }

    /**
     * Extract H000 > SystemReturnCode > ReturnCode value from the DOM XML.
     */
    public function retrieveH000ReturnCode(DOMDocument $xml): string
    {
        $xpath = $this->prepareH000XPath($xml);
        $returnCode = $xpath->query('//H000:SystemReturnCode/H000:ReturnCode');

        return SafeItems::safeItemAcces($returnCode);
    }

    /**
     * Extract H000 > SystemReturnCode > ReportText value from the DOM XML.
     */
    public function retrieveH000ReportText(DOMDocument $xml): string
    {
        $xpath = $this->prepareH000XPath($xml);
        $reportText = $xpath->query('//H000:SystemReturnCode/H000:ReportText');

        return SafeItems::safeItemAcces($reportText);
    }

    /**
     * Retrieve encoded Order data.
     *
     * @throws EbicsException
     */
    public function retrieveOrderData(DOMDocument $xml): OrderDataEncrypted
    {
        $xpath = $this->prepareH004XPath($xml);
        $orderData = $xpath->query('//H004:body/H004:DataTransfer/H004:OrderData');
        $transactionKey = $xpath->query('//H004:body/H004:DataTransfer/H004:DataEncryptionInfo/H004:TransactionKey');
        if (!$orderData || 0 === $orderData->length || !$transactionKey || 0 === $transactionKey->length) {
            throw new EbicsException('EBICS response empty result.');
        }
        $orderDataValue = SafeItems::safeItemAcces($orderData);
        $transactionKeyValue = SafeItems::safeItemAcces($transactionKey);
        $transactionKeyValueDe = base64_decode($transactionKeyValue);

        return new OrderDataEncrypted($orderDataValue, $transactionKeyValueDe);
    }

    /**
     * Extract Transaction from the DOM XML.
     */
    public function retrieveTransaction(DOMDocument $xml): Transaction
    {
        $xpath = $this->prepareH004XPath($xml);
        $transactionId = $xpath->query('//H004:header/H004:static/H004:TransactionID');
        $transactionIdValue = SafeItems::safeItemAcces($transactionId);
        $numSegments = $xpath->query('//H004:header/H004:static/H004:NumSegments');
        $numSegmentsValue = SafeItems::safeItemAcces($numSegments);
        $transactionPhase = $xpath->query('//H004:header/H004:mutable/H004:TransactionPhase');
        $transactionPhaseValue = SafeItems::safeItemAcces($transactionPhase);
        $segmentNumber = $xpath->query('//H004:header/H004:mutable/H004:SegmentNumber');
        $segmentNumberValue = SafeItems::safeItemAcces($segmentNumber);

        return TransactionFactory::buildTransaction($transactionIdValue, $transactionPhaseValue, $numSegmentsValue, $segmentNumberValue);
    }
}

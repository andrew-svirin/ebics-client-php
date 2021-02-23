<?php

namespace AndrewSvirin\Ebics\Handlers;

use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Factories\TransactionFactory;
use AndrewSvirin\Ebics\Handlers\Traits\XPathTrait;
use AndrewSvirin\Ebics\Models\OrderDataEncrypted;
use AndrewSvirin\Ebics\Models\Transaction;
use AndrewSvirin\Ebics\Services\DOMHelper;
use DOMDocument;

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
     * @var TransactionFactory
     */
    private $transactionFactory;

    public function __construct()
    {
        $this->transactionFactory = new TransactionFactory();
    }

    /**
     * Extract H004 > KeyManagementResponse > header > mutable > ReturnCode value from the DOM XML.
     *
     * @param DOMDocument $xml
     *
     * @return string
     */
    public function retrieveH004ReturnCode(DOMDocument $xml): string
    {
        $xpath = $this->prepareH004XPath($xml);
        $returnCode = $xpath->query('//H004:header/H004:mutable/H004:ReturnCode');

        return DOMHelper::safeItemValue($returnCode);
    }

    /**
     * Extract H004 > KeyManagementResponse > body > ReturnCode value from the DOM XML.
     *
     * @param DOMDocument $xml
     *
     * @return string
     */
    public function retrieveH004BodyReturnCode(DOMDocument $xml): string
    {
        $xpath = $this->prepareH004XPath($xml);
        $returnCode = $xpath->query('//H004:body/H004:ReturnCode');

        return DOMHelper::safeItemValue($returnCode);
    }

    /**
     * Extract H004 > ReturnCode value from both header and body.
     * Sometimes (FrenchBank) header code is 00000 whereas body return isn't...
     *
     * @param DOMDocument $xml
     *
     * @return string
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
     *
     * @param DOMDocument $xml
     *
     * @return string
     */
    public function retrieveH004ReportText(DOMDocument $xml): string
    {
        $xpath = $this->prepareH004XPath($xml);
        $reportText = $xpath->query('//H004:header/H004:mutable/H004:ReportText');

        return DOMHelper::safeItemValue($reportText);
    }

    /**
     * Extract H000 > SystemReturnCode > ReturnCode value from the DOM XML.
     *
     * @param DOMDocument $xml
     *
     * @return string
     */
    public function retrieveH000ReturnCode(DOMDocument $xml): string
    {
        $xpath = $this->prepareH000XPath($xml);
        $returnCode = $xpath->query('//H000:SystemReturnCode/H000:ReturnCode');

        return DOMHelper::safeItemValue($returnCode);
    }

    /**
     * Extract H000 > SystemReturnCode > ReportText value from the DOM XML.
     *
     * @param DOMDocument $xml
     *
     * @return string
     */
    public function retrieveH000ReportText(DOMDocument $xml): string
    {
        $xpath = $this->prepareH000XPath($xml);
        $reportText = $xpath->query('//H000:SystemReturnCode/H000:ReportText');

        return DOMHelper::safeItemValue($reportText);
    }

    /**
     * Retrieve encoded Order data.
     *
     * @param DOMDocument $xml
     *
     * @return OrderDataEncrypted
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
        $orderDataValue = DOMHelper::safeItemValue($orderData);
        $transactionKeyValue = DOMHelper::safeItemValue($transactionKey);
        $transactionKeyValueDe = base64_decode($transactionKeyValue);

        return new OrderDataEncrypted($orderDataValue, $transactionKeyValueDe);
    }

    /**
     * Extract Transaction from the DOM XML.
     *
     * @param DOMDocument $xml
     *
     * @return Transaction
     */
    public function retrieveTransaction(DOMDocument $xml): Transaction
    {
        $xpath = $this->prepareH004XPath($xml);
        $transactionIdPath = $xpath->query('//H004:header/H004:static/H004:TransactionID');
        $transactionId = DOMHelper::safeItemValue($transactionIdPath);
        $transactionPhasePath = $xpath->query('//H004:header/H004:mutable/H004:TransactionPhase');
        $transactionPhase = DOMHelper::safeItemValue($transactionPhasePath);
        $numSegmentsPath = $xpath->query('//H004:header/H004:static/H004:NumSegments');
        $numSegments = DOMHelper::safeItemValueOrNull($numSegmentsPath);
        $orderIdPath = $xpath->query('//H004:header/H004:mutable/H004:OrderID');
        $orderId = DOMHelper::safeItemValueOrNull($orderIdPath);
        $segmentNumberPath = $xpath->query('//H004:header/H004:mutable/H004:SegmentNumber');
        $segmentNumber = DOMHelper::safeItemValueOrNull($segmentNumberPath);

        $transaction = $this->transactionFactory->create($transactionId, $transactionPhase);
        $transaction->setNumSegments(null !== $numSegments ? (int)$numSegments : null);
        $transaction->setOrderId($orderId);
        $transaction->setSegmentNumber(null !== $segmentNumber ? (int)$segmentNumber : null);

        return $transaction;
    }
}

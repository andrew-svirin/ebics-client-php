<?php

namespace AndrewSvirin\Ebics\Handlers;

use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Factories\OrderDataFactory;
use AndrewSvirin\Ebics\Factories\TransactionFactory;
use AndrewSvirin\Ebics\Handlers\Traits\XPathTrait;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Models\OrderData;
use AndrewSvirin\Ebics\Models\Transaction;
use AndrewSvirin\Ebics\Services\CryptService;
use AndrewSvirin\Ebics\Services\DOMHelper;
use AndrewSvirin\Ebics\Services\ZipService;
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
     * @var OrderDataFactory
     */
    private $orderDataFactory;

    /**
     * @var TransactionFactory
     */
    private $transactionFactory;

    /**
     * @var CryptService
     */
    private $cryptService;

    /**
     * @var ZipService
     */
    private $zipService;

    public function __construct()
    {
        $this->orderDataFactory = new OrderDataFactory();
        $this->transactionFactory = new TransactionFactory();
        $this->cryptService = new CryptService();
        $this->zipService = new ZipService();
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
     * Retrieve OrderData.
     *
     * @param DOMDocument $xml
     * @param string $transactionKey
     * @param KeyRing $keyRing
     *
     * @return OrderData
     * @throws EbicsException
     */
    public function retrieveOrderData(DOMDocument $xml, string $transactionKey, KeyRing $keyRing): OrderData
    {
        $plainOrderData = $this->retrievePlainOrderData($xml, $transactionKey, $keyRing);
        $orderData = $this->orderDataFactory->createOrderDataFromContent($plainOrderData);

        return $orderData;
    }

    /**
     * Retrieve OrderData items.
     * Unzip order data items.
     *
     * @param DOMDocument $xml
     * @param string $transactionKey
     * @param KeyRing $keyRing
     *
     * @return OrderData[]
     * @throws EbicsException
     */
    public function retrieveOrderDataItems(DOMDocument $xml, string $transactionKey, KeyRing $keyRing): array
    {
        $plainOrderData = $this->retrievePlainOrderData($xml, $transactionKey, $keyRing);

        $orderDataXmlItems = $this->zipService->extractFilesFromString($plainOrderData);

        $orderDataItems = [];
        foreach ($orderDataXmlItems as $orderDataXmlItem) {
            $orderDataItems[] = $this->orderDataFactory->createOrderDataFromContent($orderDataXmlItem);
        }

        return $orderDataItems;
    }

    /**
     * Retrieve plain OrderData.
     *
     * @param DOMDocument $xml
     * @param string $transactionKey
     * @param KeyRing $keyRing
     *
     * @return string
     * @throws EbicsException
     */
    public function retrievePlainOrderData(DOMDocument $xml, string $transactionKey, KeyRing $keyRing)
    {
        $xpath = $this->prepareH004XPath($xml);
        $orderDataPath = $xpath->query('//H004:body/H004:DataTransfer/H004:OrderData');
        if (!$orderDataPath || 0 === $orderDataPath->length) {
            throw new EbicsException('EBICS response empty result.');
        }
        $plainOrderDataEncrypted = DOMHelper::safeItemValue($orderDataPath);

        $plainOrderDataCompressed = $this->cryptService->decryptPlainOrderDataCompressed(
            $keyRing,
            $plainOrderDataEncrypted,
            $transactionKey
        );

        $plainOrderData = $this->zipService->uncompress($plainOrderDataCompressed);

        return $plainOrderData;
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
        $transactionId = DOMHelper::safeItemValueOrNull($transactionIdPath);
        $transactionPhasePath = $xpath->query('//H004:header/H004:mutable/H004:TransactionPhase');
        $transactionPhase = DOMHelper::safeItemValueOrNull($transactionPhasePath);
        $numSegmentsPath = $xpath->query('//H004:header/H004:static/H004:NumSegments');
        $numSegments = DOMHelper::safeItemValueOrNull($numSegmentsPath);
        $orderIdPath = $xpath->query('//H004:header/H004:mutable/H004:OrderID');
        $orderId = DOMHelper::safeItemValueOrNull($orderIdPath);
        $segmentNumberPath = $xpath->query('//H004:header/H004:mutable/H004:SegmentNumber');

        // is segment number required? otherwise "safeItemOrNull" is needed
        $segmentNumberDom = DOMHelper::safeItem($segmentNumberPath);

        $lastSegment = $segmentNumberDom->getAttribute('lastSegment');
        $segmentNumber = $segmentNumberDom->nodeValue;


        $transactionKeyPath = $xpath->query(
            '//H004:body/H004:DataTransfer/H004:DataEncryptionInfo/H004:TransactionKey'
        );
        $transactionKeyEncoded = DOMHelper::safeItemValueOrNull($transactionKeyPath);
        $transactionKey = base64_decode($transactionKeyEncoded);

        $transaction = $this->transactionFactory->create();
        $transaction->setId($transactionId);
        $transaction->setPhase($transactionPhase);
        $transaction->setNumSegments(null !== $numSegments ? (int)$numSegments : null);
        $transaction->setOrderId($orderId);
        $transaction->setSegmentNumber(null !== $segmentNumber ? (int)$segmentNumber : null);
        $transaction->setLastSegment((bool)$lastSegment ?? null);
        $transaction->setKey($transactionKey);

        return $transaction;
    }
}

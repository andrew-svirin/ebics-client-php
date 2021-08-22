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
abstract class ResponseHandler
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
     * Extract H00X > KeyManagementResponse > header > mutable > ReturnCode value from the DOM XML.
     *
     * @param DOMDocument $xml
     *
     * @return string
     */
    abstract public function retrieveH00XReturnCode(DOMDocument $xml): string;

    /**
     * Extract H00X > KeyManagementResponse > body > ReturnCode value from the DOM XML.
     *
     * @param DOMDocument $xml
     *
     * @return string
     */
    abstract public function retrieveH00XBodyReturnCode(DOMDocument $xml): string;

    /**
     * Extract H00X > ReturnCode value from both header and body.
     * Sometimes (FrenchBank) header code is 00000 whereas body return isn't...
     *
     * @param DOMDocument $xml
     *
     * @return string
     */
    public function retrieveH00XBodyOrHeaderReturnCode(DOMDocument $xml): string
    {
        $headerReturnCode = $this->retrieveH00XReturnCode($xml);
        $bodyReturnCode = $this->retrieveH00XBodyReturnCode($xml);

        if ('000000' !== $headerReturnCode) {
            return $headerReturnCode;
        }

        return $bodyReturnCode;
    }

    /**
     * Extract H00X > KeyManagementResponse > header > mutable > ReportText value from the DOM XML.
     *
     * @param DOMDocument $xml
     *
     * @return string
     */
    abstract public function retrieveH00XReportText(DOMDocument $xml): string;

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

    abstract public function retrieveH00XTransactionId(DOMDocument $xml): ?string;

    abstract public function retrieveH00XTransactionPhase(DOMDocument $xml): ?string;

    abstract public function retrieveH00XNumSegments(DOMDocument $xml): ?string;

    abstract public function retrieveH00XOrderId(DOMDocument $xml): ?string;

    abstract public function retrieveH00XSegmentNumber(DOMDocument $xml): ?string;

    abstract public function retrieveH00XTransactionKey(DOMDocument $xml): ?string;

    abstract public function retrieveH00XOrderData(DOMDocument $xml): ?string;

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
    public function retrieveOrderDataItems(
        DOMDocument $xml,
        string $transactionKey,
        KeyRing $keyRing
    ): array {
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
    public function retrievePlainOrderData(DOMDocument $xml, string $transactionKey, KeyRing $keyRing): string
    {
        $plainOrderDataEncrypted = $this->retrieveH00XOrderData($xml);
        if (null === $plainOrderDataEncrypted) {
            throw new EbicsException('EBICS response empty result.');
        }

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
        $transactionId = $this->retrieveH00XTransactionId($xml);
        $transactionPhase = $this->retrieveH00XTransactionPhase($xml);
        $numSegments = $this->retrieveH00XNumSegments($xml);
        $orderId = $this->retrieveH00XOrderId($xml);
        $segmentNumber = $this->retrieveH00XSegmentNumber($xml);
        $transactionKeyEncoded = $this->retrieveH00XTransactionKey($xml);
        $transactionKey = base64_decode($transactionKeyEncoded);

        $transaction = $this->transactionFactory->create();
        $transaction->setId($transactionId);
        $transaction->setPhase($transactionPhase);
        $transaction->setNumSegments(null !== $numSegments ? (int)$numSegments : null);
        $transaction->setOrderId($orderId);
        $transaction->setSegmentNumber(null !== $segmentNumber ? (int)$segmentNumber : null);
        $transaction->setKey($transactionKey);

        return $transaction;
    }
}

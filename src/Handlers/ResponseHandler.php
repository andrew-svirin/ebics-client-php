<?php

namespace AndrewSvirin\Ebics\Handlers;

use AndrewSvirin\Ebics\EbicsClient;
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
     * Extract H005 > KeyManagementResponse > header > mutable > ReturnCode value from the DOM XML.
     *
     * @param DOMDocument $xml
     *
     * @return string
     */
    public function retrieveH005ReturnCode(DOMDocument $xml): string
    {
        $xpath = $this->prepareH005XPath($xml);
        $returnCode = $xpath->query('//H005:header/H005:mutable/H005:ReturnCode');

        return DOMHelper::safeItemValue($returnCode);
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
     * Extract H005 > KeyManagementResponse > body > ReturnCode value from the DOM XML.
     *
     * @param DOMDocument $xml
     *
     * @return string
     */
    public function retrieveH005BodyReturnCode(DOMDocument $xml): string
    {
        $xpath = $this->prepareH005XPath($xml);
        $returnCode = $xpath->query('//H005:body/H005:ReturnCode');

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
     * Extract H005 > ReturnCode value from both header and body.
     * Sometimes (FrenchBank) header code is 00000 whereas body return isn't...
     *
     * @param DOMDocument $xml
     *
     * @return string
     */
    public function retrieveH005BodyOrHeaderReturnCode(DOMDocument $xml): string
    {
        $headerReturnCode = $this->retrieveH005ReturnCode($xml);
        $bodyReturnCode = $this->retrieveH005BodyReturnCode($xml);

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
     * Extract H005 > KeyManagementResponse > header > mutable > ReportText value from the DOM XML.
     *
     * @param DOMDocument $xml
     *
     * @return string
     */
    public function retrieveH005ReportText(DOMDocument $xml): string
    {
        $xpath = $this->prepareH005XPath($xml);
        $reportText = $xpath->query('//H005:header/H005:mutable/H005:ReportText');

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
    public function retrieveOrderData(DOMDocument $xml, string $transactionKey, KeyRing $keyRing, string $ebicsVersion): OrderData
    {
        $plainOrderData = $this->retrievePlainOrderData($xml, $transactionKey, $keyRing, $ebicsVersion);
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
    public function retrieveOrderDataItems(DOMDocument $xml, string $transactionKey, KeyRing $keyRing, string $ebicsVersion): array
    {
        $plainOrderData = $this->retrievePlainOrderData($xml, $transactionKey, $keyRing, $ebicsVersion);

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
     * @param string $ebicsVersion
     *
     * @return string
     * @throws EbicsException
     */
    public function retrievePlainOrderData(DOMDocument $xml, string $transactionKey, KeyRing $keyRing, string $ebicsVersion)
    {
        switch ($ebicsVersion) {
            case EbicsClient::VERSION_30:
                $ebicsSchema = "H005";
                $xpath = $this->prepareH005XPath($xml);
                break;
            
            case EbicsClient::VERSION_25:
                $ebicsSchema = "H004";
                $xpath = $this->prepareH004XPath($xml);
                break;
        }

        $orderDataPath = $xpath->query("//$ebicsSchema:body/$ebicsSchema:DataTransfer/$ebicsSchema:OrderData");
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
     * @param string $ebicsVersion
     *
     * @return Transaction
     */
    public function retrieveTransaction(DOMDocument $xml, string $ebicsVersion): Transaction
    {
        switch ($ebicsVersion) {
            case EbicsClient::VERSION_30:
                $ebicsSchema = "H005";
                $xpath = $this->prepareH005XPath($xml);
                break;
            
            case EbicsClient::VERSION_25:
                $ebicsSchema = "H004";
                $xpath = $this->prepareH004XPath($xml);
                break;
        }

        $transactionIdPath = $xpath->query("//$ebicsSchema:header/$ebicsSchema:static/$ebicsSchema:TransactionID");
        $transactionId = DOMHelper::safeItemValueOrNull($transactionIdPath);
        $transactionPhasePath = $xpath->query("//$ebicsSchema:header/$ebicsSchema:mutable/$ebicsSchema:TransactionPhase");
        $transactionPhase = DOMHelper::safeItemValueOrNull($transactionPhasePath);
        $numSegmentsPath = $xpath->query("//$ebicsSchema:header/$ebicsSchema:static/$ebicsSchema:NumSegments");
        $numSegments = DOMHelper::safeItemValueOrNull($numSegmentsPath);
        $orderIdPath = $xpath->query("//$ebicsSchema:header/$ebicsSchema:mutable/$ebicsSchema:OrderID");
        $orderId = DOMHelper::safeItemValueOrNull($orderIdPath);
        $segmentNumberPath = $xpath->query("//$ebicsSchema:header/$ebicsSchema:mutable/$ebicsSchema:SegmentNumber");
        $segmentNumber = DOMHelper::safeItemValueOrNull($segmentNumberPath);
        $transactionKeyPath = $xpath->query(
            "//$ebicsSchema:body/$ebicsSchema:DataTransfer/$ebicsSchema:DataEncryptionInfo/$ebicsSchema:TransactionKey"
        );
        $transactionKeyEncoded = DOMHelper::safeItemValueOrNull($transactionKeyPath);
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

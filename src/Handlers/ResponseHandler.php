<?php

namespace AndrewSvirin\Ebics\Handlers;

use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Factories\SegmentFactory;
use AndrewSvirin\Ebics\Handlers\Traits\H00XTrait;
use AndrewSvirin\Ebics\Models\DownloadSegment;
use AndrewSvirin\Ebics\Models\Http\Request;
use AndrewSvirin\Ebics\Models\Http\Response;
use AndrewSvirin\Ebics\Models\InitializationSegment;
use AndrewSvirin\Ebics\Models\Keyring;
use AndrewSvirin\Ebics\Models\UploadSegment;
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
    use H00XTrait;

    protected SegmentFactory $segmentFactory;
    protected CryptService $cryptService;
    protected ZipService $zipService;

    public function __construct()
    {
        $this->segmentFactory = new SegmentFactory();
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
    public function retrieveH00XReturnCode(DOMDocument $xml): string
    {
        return DOMHelper::safeItemValue($this->queryH00XXpath($xml, '//header/mutable/ReturnCode'));
    }

    /**
     * Extract H00X > KeyManagementResponse > body > ReturnCode value from the DOM XML.
     *
     * @param DOMDocument $xml
     *
     * @return string
     */
    public function retrieveH00XBodyReturnCode(DOMDocument $xml): string
    {
        return DOMHelper::safeItemValue($this->queryH00XXpath($xml, '//body/ReturnCode'));
    }

    /**
     * Extract H00X > KeyManagementResponse > header > mutable > ReportText value from the DOM XML.
     *
     * @param DOMDocument $xml
     *
     * @return string
     */
    public function retrieveH00XReportText(DOMDocument $xml): string
    {
        return DOMHelper::safeItemValue($this->queryH00XXpath($xml, '//header/mutable/ReportText'));
    }

    public function retrieveH00XTransactionId(DOMDocument $xml): ?string
    {
        return DOMHelper::safeItemValueOrNull($this->queryH00XXpath($xml, '//header/static/TransactionID'));
    }

    public function retrieveH00XTransactionPhase(DOMDocument $xml): ?string
    {
        return DOMHelper::safeItemValueOrNull($this->queryH00XXpath($xml, '//header/mutable/TransactionPhase'));
    }

    public function retrieveH00XNumSegments(DOMDocument $xml): ?string
    {
        return DOMHelper::safeItemValueOrNull($this->queryH00XXpath($xml, '//header/static/NumSegments'));
    }

    public function retrieveH00XRequestOrderId(DOMDocument $xml): string
    {
        $xpath = $this->prepareH00XXPath($xml);
        $value = $xpath->query("//header/static/OrderDetails/OrderID");

        return DOMHelper::safeItemValueOrNull($value);
    }

    public function retrieveH00XResponseOrderId(DOMDocument $xml): string
    {
        return DOMHelper::safeItemValueOrNull($this->queryH00XXpath($xml, '//header/mutable/OrderID'));
    }

    public function retrieveH00XSegmentNumber(DOMDocument $xml): ?string
    {
        return DOMHelper::safeItemValueOrNull($this->queryH00XXpath($xml, '//header/mutable/SegmentNumber'));
    }

    public function retrieveH00XTransactionKey(DOMDocument $xml): ?string
    {
        return DOMHelper::safeItemValueOrNull(
            $this->queryH00XXpath($xml, '//body/DataTransfer/DataEncryptionInfo/TransactionKey')
        );
    }

    public function retrieveH00XOrderData(DOMDocument $xml): ?string
    {
        return DOMHelper::safeItemValueOrNull($this->queryH00XXpath($xml, '//body/DataTransfer/OrderData'));
    }

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
     * Extract InitializationSegment from the DOM XML.
     *
     * @throws EbicsException
     */
    public function extractInitializationSegment(Response $response, Keyring $keyring): InitializationSegment
    {
        $transactionKeyEncoded = $this->retrieveH00XTransactionKey($response);
        $transactionKey = base64_decode($transactionKeyEncoded);
        $orderDataEncrypted = $this->retrieveH00XOrderData($response);
        $orderDataCompressed = $this->cryptService->decryptOrderDataCompressed(
            $keyring,
            $orderDataEncrypted,
            $transactionKey
        );
        $orderData = $this->zipService->uncompress($orderDataCompressed);

        $segment = $this->segmentFactory->createInitializationSegment();
        $segment->setResponse($response);
        $segment->setTransactionKey($transactionKey);
        $segment->setOrderData($orderData);

        return $segment;
    }

    /**
     * Extract DownloadSegment from the DOM XML.
     */
    public function extractDownloadSegment(Response $response): DownloadSegment
    {
        $transactionId = $this->retrieveH00XTransactionId($response);
        $transactionPhase = $this->retrieveH00XTransactionPhase($response);
        $transactionKeyEncoded = $this->retrieveH00XTransactionKey($response);
        $transactionKey = base64_decode($transactionKeyEncoded);
        $numSegments = $this->retrieveH00XNumSegments($response);
        $segmentNumber = $this->retrieveH00XSegmentNumber($response);
        $orderDataEncrypted = $this->retrieveH00XOrderData($response);
        $segment = $this->segmentFactory->createDownloadSegment();
        $segment->setResponse($response);
        $segment->setTransactionId($transactionId);
        $segment->setTransactionPhase($transactionPhase);
        $segment->setTransactionKey($transactionKey);
        $segment->setNumSegments((int)$numSegments);
        $segment->setSegmentNumber((int)$segmentNumber);
        $segment->setOrderData($orderDataEncrypted);

        return $segment;
    }

    /**
     * Extract DownloadSegment from the DOM XML.
     */
    abstract public function extractUploadSegment(Request $request, Response $response): UploadSegment;
}

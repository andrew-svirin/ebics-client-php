<?php

namespace AndrewSvirin\Ebics\Handlers;

use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Factories\SegmentFactory;
use AndrewSvirin\Ebics\Handlers\Traits\XPathTrait;
use AndrewSvirin\Ebics\Models\DownloadSegment;
use AndrewSvirin\Ebics\Models\Http\Response;
use AndrewSvirin\Ebics\Models\InitializationSegment;
use AndrewSvirin\Ebics\Models\KeyRing;
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
    use XPathTrait;

    private SegmentFactory $segmentFactory;
    private CryptService $cryptService;
    private ZipService $zipService;

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
     * Extract InitializationSegment from the DOM XML.
     * @throws EbicsException
     */
    public function extractInitializationSegment(Response $response, KeyRing $keyRing): InitializationSegment
    {
        $transactionKeyEncoded = $this->retrieveH00XTransactionKey($response);
        $transactionKey = base64_decode($transactionKeyEncoded);
        $orderDataEncrypted = $this->retrieveH00XOrderData($response);
        $orderDataCompressed = $this->cryptService->decryptOrderDataCompressed(
            $keyRing,
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
     * @throws EbicsException
     */
    public function extractDownloadSegment(Response $response, KeyRing $keyRing): DownloadSegment
    {
        $transactionId = $this->retrieveH00XTransactionId($response);
        $transactionPhase = $this->retrieveH00XTransactionPhase($response);
        $transactionKeyEncoded = $this->retrieveH00XTransactionKey($response);
        $transactionKey = base64_decode($transactionKeyEncoded);
        $numSegments = $this->retrieveH00XNumSegments($response);
        $segmentNumber = $this->retrieveH00XSegmentNumber($response);
        $orderDataEncrypted = $this->retrieveH00XOrderData($response);
        $orderDataCompressed = $this->cryptService->decryptOrderDataCompressed(
            $keyRing,
            $orderDataEncrypted,
            $transactionKey
        );
        $orderData = $this->zipService->uncompress($orderDataCompressed);

        $segment = $this->segmentFactory->createDownloadSegment();
        $segment->setResponse($response);
        $segment->setTransactionId($transactionId);
        $segment->setTransactionPhase($transactionPhase);
        $segment->setTransactionKey($transactionKey);
        $segment->setNumSegments((int)$numSegments);
        $segment->setSegmentNumber((int)$segmentNumber);
        $segment->setOrderData($orderData);

        return $segment;
    }

    /**
     * Extract DownloadSegment from the DOM XML.
     */
    public function extractUploadSegment(Response $response): UploadSegment
    {
        $transactionId = $this->retrieveH00XTransactionId($response);
        $transactionPhase = $this->retrieveH00XTransactionPhase($response);
        $orderId = $this->retrieveH00XOrderId($response);

        $segment = $this->segmentFactory->createUploadSegment();
        $segment->setResponse($response);
        $segment->setTransactionId($transactionId);
        $segment->setTransactionPhase($transactionPhase);
        $segment->setOrderId($orderId);

        return $segment;
    }
}

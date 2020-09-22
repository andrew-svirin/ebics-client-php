<?php

namespace AndrewSvirin\Ebics\Handlers;

use AndrewSvirin\Ebics\Contracts\EbicsResponseExceptionInterface;
use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Factories\EbicsExceptionFactory;
use AndrewSvirin\Ebics\Factories\TransactionFactory;
use AndrewSvirin\Ebics\Handlers\Traits\XPathTrait;
use AndrewSvirin\Ebics\Models\OrderDataEncrypted;
use AndrewSvirin\Ebics\Models\Request;
use AndrewSvirin\Ebics\Models\Response;
use AndrewSvirin\Ebics\Models\Transaction;
use AndrewSvirin\Ebics\Models\Version;
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
     * Extract H004 > KeyManagementResponse > header > mutable > ReturnCode value from the DOM XML.
     */
    public function retrieveH004ReturnCode(Response $xml): string
    {
        $xpath = $this->prepareH004XPath($xml, $xml->getVersion());
        $returnCode = $xpath->query('//'.$xml->getVersion().':header/'.$xml->getVersion().':mutable/'.$xml->getVersion().':ReturnCode');

        return DOMHelper::safeItemValue($returnCode);
    }

    /**
     * Extract H004 > KeyManagementResponse > body > ReturnCode value from the DOM XML.
     */
    public function retrieveH004BodyReturnCode(Response $xml): string
    {
        $xpath = $this->prepareH004XPath($xml, $xml->getVersion());
        $returnCode = $xpath->query('//'.$xml->getVersion().':body/'.$xml->getVersion().':ReturnCode');

        return DOMHelper::safeItemValue($returnCode);
    }

    /**
     * Extract H004 > ReturnCode value from both header and body. Sometimes (FrenchBank) header code is 00000 whereas body return isn't...
     */
    public function retrieveH004BodyOrHeaderReturnCode(Response $xml): string
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
    public function retrieveH004ReportText(Response $xml): string
    {
        $xpath = $this->prepareH004XPath($xml, $xml->getVersion());
        $reportText = $xpath->query('//'.$xml->getVersion().':header/'.$xml->getVersion().':mutable/'.$xml->getVersion().':ReportText');

        return DOMHelper::safeItemValue($reportText);
    }

    /**
     * Extract H000 > SystemReturnCode > ReturnCode value from the DOM XML.
     */
    public function retrieveH000ReturnCode(Response $xml): string
    {
        $xpath = $this->prepareH000XPath($xml);
        $returnCode = $xpath->query('//H000:SystemReturnCode/H000:ReturnCode');

        return DOMHelper::safeItemValue($returnCode);
    }

    /**
     * Extract H000 > SystemReturnCode > ReportText value from the DOM XML.
     */
    public function retrieveH000ReportText(Response $xml): string
    {
        $xpath = $this->prepareH000XPath($xml);
        $reportText = $xpath->query('//H000:SystemReturnCode/H000:ReportText');

        return DOMHelper::safeItemValue($reportText);
    }

    /**
     * Retrieve encoded Order data.
     *
     * @throws EbicsException
     */
    public function retrieveOrderData(Response $xml): OrderDataEncrypted
    {
        $xpath = $this->prepareH004XPath($xml);
        $orderData = $xpath->query('//'.$xml->getVersion().':body/'.$xml->getVersion().':DataTransfer/'.$xml->getVersion().':OrderData');
        $transactionKey = $xpath->query('//'.$xml->getVersion().':body/'.$xml->getVersion().':DataTransfer/'.$xml->getVersion().':DataEncryptionInfo/'.$xml->getVersion().':TransactionKey');
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
     */
    public function retrieveTransaction(Response $xml): Transaction
    {
        $xpath = $this->prepareH004XPath($xml);
        $transactionId = $xpath->query('//'.$xml->getVersion().':header/'.$xml->getVersion().':static/'.$xml->getVersion().':TransactionID');
        $transactionIdValue = DOMHelper::safeItemValue($transactionId);
        $numSegments = $xpath->query('//'.$xml->getVersion().':header/'.$xml->getVersion().':static/'.$xml->getVersion().':NumSegments');
        $numSegmentsValue = DOMHelper::safeItemValue($numSegments);
        $transactionPhase = $xpath->query('//'.$xml->getVersion().':header/'.$xml->getVersion().':mutable/'.$xml->getVersion().':TransactionPhase');
        $transactionPhaseValue = DOMHelper::safeItemValue($transactionPhase);
        $segmentNumber = $xpath->query('//'.$xml->getVersion().':header/'.$xml->getVersion().':mutable/'.$xml->getVersion().':SegmentNumber');
        $segmentNumberValue = DOMHelper::safeItemValue($segmentNumber);

        return Transaction::buildTransaction($transactionIdValue, $transactionPhaseValue, (int) $numSegmentsValue, (int) $segmentNumberValue);
    }

    /**
     * @throws EbicsResponseExceptionInterface
     */
    public function checkH000ReturnCode(Request $request, Response $response): Response
    {
        $errorCode = $this->retrieveH000ReturnCode($response);

        if ('000000' === $errorCode) {
            return $response;
        }

        $reportText = $this->retrieveH000ReportText($response);
        throw EbicsExceptionFactory::buildExceptionFromCode($errorCode, $reportText, $request, $response);
    }

    /**
     * @throws EbicsResponseExceptionInterface
     */
    public function checkH004ReturnCode(Request $request, Response $response): void
    {
        $errorCode = $this->retrieveH004BodyOrHeaderReturnCode($response);

        if ('000000' === $errorCode) {
            return;
        }

        $reportText = $this->retrieveH004ReportText($response);
        throw EbicsExceptionFactory::buildExceptionFromCode($errorCode, $reportText, $request, $response);
    }
}

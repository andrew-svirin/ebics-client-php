<?php

namespace AndrewSvirin\Ebics\Contracts;

use AndrewSvirin\Ebics\Contexts\BTFContext;
use AndrewSvirin\Ebics\Models\Http\Response;
use DateTimeInterface;

/**
 * EBICS client representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
interface EbicsClientInterface
{
    /**
     * Create user signatures A, E and X on first launch.
     */
    public function createUserSignatures(): void;

    /**
     * Supported protocol version for the Bank.
     *
     * @return Response
     */
    public function HEV(): Response;

    /**
     * Make INI request.
     * Send to the bank public signature of signature A005.
     * Prepare A005 signature for KeyRing.
     *
     * @param DateTimeInterface|null $dateTime current date
     *
     * @return Response
     */
    public function INI(DateTimeInterface $dateTime = null): Response;

    /**
     * Make HIA request.
     * Send to the bank public signatures of authentication (X002) and encryption (E002).
     * Prepare E002 and X002 user signatures for KeyRing.
     *
     * @param DateTimeInterface|null $dateTime current date
     *
     * @return Response
     */
    public function HIA(DateTimeInterface $dateTime = null): Response;

    /**
     * Make BTD request.
     * Download request (FETCH request)
     * @requires Ebics 3.0
     */
    public function BTD(
        BTFContext $btfContext,
        DateTimeInterface $dateTime = null,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null
    ): string;

    /**
     * Retrieve the Bank public signatures authentication (X002) and encryption (E002).
     * Decrypt OrderData.
     * Prepare E002 and X002 bank signatures for KeyRing.
     *
     * @param DateTimeInterface|null $dateTime current date
     *
     * @return Response
     */
    public function HPB(DateTimeInterface $dateTime = null): Response;

    /**
     * Retrieve the bank server parameters.
     * Send self::transferReceipt() after transaction finished.
     *
     * @param DateTimeInterface|null $dateTime
     *
     * @return Response
     */
    public function HPD(DateTimeInterface $dateTime = null): Response;

    /**
     * Retrieve customer's customer and subscriber information.
     * Send self::transferReceipt() after transaction finished.
     *
     * @param DateTimeInterface|null $dateTime
     *
     * @return Response
     */
    public function HKD(DateTimeInterface $dateTime = null): Response;

    /**
     * Retrieve subscriber's customer and subscriber information.
     * Send self::transferReceipt() after transaction finished.
     *
     * @param DateTimeInterface|null $dateTime
     *
     * @return Response
     */
    public function HTD(DateTimeInterface $dateTime = null): Response;

    /**
     * Use PTK order type to download transaction status.
     * Send self::transferReceipt() after transaction finished.
     *
     * @param DateTimeInterface|null $dateTime
     *
     * @return Response
     */
    public function PTK(DateTimeInterface $dateTime = null): Response;

    /**
     * Retrieve subscriber's customer and subscriber information.
     * Send self::transferReceipt() after transaction finished.
     *
     * @param string $fileInfo
     * @param string $format = 'plain' ?? 'xml'
     * @param string $countryCode
     * @param DateTimeInterface|null $dateTime
     * @param DateTimeInterface|null $startDateTime
     * @param DateTimeInterface|null $endDateTime
     *
     * @return Response
     */
    public function FDL(
        string $fileInfo,
        string $format = 'plain',
        string $countryCode = 'FR',
        DateTimeInterface $dateTime = null,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null
    ): Response;

    /**
     * Retrieve  Bank available order types.
     * Send self::transferReceipt() after transaction finished.
     *
     * @param DateTimeInterface|null $dateTime current date
     *
     * @return Response
     */
    public function HAA(DateTimeInterface $dateTime = null): Response;

    /**
     * Downloads the interim transaction report in SWIFT format (MT942).
     * Send self::transferReceipt() after transaction finished.
     *
     * @param DateTimeInterface|null $dateTime current date
     * @param DateTimeInterface|null $startDateTime the start date of requested transactions
     * @param DateTimeInterface|null $endDateTime the end date of requested transactions
     *
     * @return Response
     */
    public function VMK(
        DateTimeInterface $dateTime = null,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null
    ): Response;

    /**
     * Retrieve the bank account statement.
     * Send self::transferReceipt() after transaction finished.
     *
     * @param DateTimeInterface|null $dateTime
     * @param DateTimeInterface|null $startDateTime the start date of requested transactions
     * @param DateTimeInterface|null $endDateTime the end date of requested transactions
     *
     * @return Response
     */
    public function STA(
        DateTimeInterface $dateTime = null,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null
    ): Response;

    /**
     * Retrieve the bank account statement in Camt.053 format.
     * Send self::transferReceipt() after transaction finished.
     *
     * @param DateTimeInterface|null $dateTime
     * @param DateTimeInterface|null $startDateTime the start date of requested transactions
     * @param DateTimeInterface|null $endDateTime the end date of requested transactions
     *
     * @return Response
     */
    // @codingStandardsIgnoreStart
    public function C53(
        DateTimeInterface $dateTime = null,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null
    ): Response;
    // @codingStandardsIgnoreEnd
    /**
     * Another way to retrieve the bank account statement in Camt.053 format (i.e Switzerland financial services).
     * Send self::transferReceipt() after transaction finished.
     *
     * @param DateTimeInterface|null $dateTime
     * @param DateTimeInterface|null $startDateTime the start date of requested transactions
     * @param DateTimeInterface|null $endDateTime the end date of requested transactions
     *
     * @return Response
     */
    // @codingStandardsIgnoreStart
    public function Z53(
        DateTimeInterface $dateTime = null,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null
    ): Response;
    // @codingStandardsIgnoreEnd

    /**
     * Retrieve a bank account statement in Camt.054 format (i.e available in Switzerland)
     * Send self::transferReceipt() after transaction finished.
     *
     * @param DateTimeInterface|null $dateTime
     * @param DateTimeInterface|null $startDateTime the start date of requested transactions
     * @param DateTimeInterface|null $endDateTime the end date of requested transactions
     *
     * @return Response zipped camt.054 XML files.
     */
    // @codingStandardsIgnoreStart
    public function Z54(
        DateTimeInterface $dateTime = null,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null
    ): Response;
    // @codingStandardsIgnoreEnd

    /**
     * Using the CCT order type, the user can initiate the credit transfer per Single Euro Payments Area (SEPA)
     * specification set by the European Payment Council or Die Deutsche Kreditwirtschaft (DK (German)).
     *
     * CCT is an upload order type that uses the protocol version H00X.
     *
     * FileFormat pain.001.001.03
     *
     * @param OrderDataInterface $orderData
     * @param DateTimeInterface|null $dateTime
     * @param int $numSegments
     *
     * @return Response
     */
    public function CCT(
        OrderDataInterface $orderData,
        DateTimeInterface $dateTime = null,
        int $numSegments = 1
    ): Response;

    /**
     * Using the CDD order type the user can initiate a direct debit transaction.
     *
     * The CDD order type uses the protocol version H00X.
     *
     * FileFormat pain.008.001.02
     *
     * @param OrderDataInterface $orderData
     * @param DateTimeInterface|null $dateTime
     * @param int $numSegments
     *
     * @return Response
     */
    public function CDD(
        OrderDataInterface $orderData,
        DateTimeInterface $dateTime = null,
        int $numSegments = 1
    ): Response;

    /**
     * Mark transactions as received.
     *
     * @param Response $response
     * @param bool $acknowledged
     *
     * @return Response
     */
    public function transferReceipt(Response $response, bool $acknowledged = true): Response;

    /**
     * Mark transactions as transferred.
     *
     * @param Response $response
     *
     * @return Response
     */
    public function transferTransfer(Response $response): Response;

    /**
     * Set certificate X509 Generator for French bank.
     *
     * @param X509GeneratorInterface|null $x509Generator
     */
    public function setX509Generator(X509GeneratorInterface $x509Generator = null): void;

    /**
     * Set http client to subset later in the project.
     *
     * @param HttpClientInterface $httpClient
     */
    public function setHttpClient(HttpClientInterface $httpClient): void;
}

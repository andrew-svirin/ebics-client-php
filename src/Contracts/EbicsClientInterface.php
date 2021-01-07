<?php

namespace AndrewSvirin\Ebics\Contracts;

use AndrewSvirin\Ebics\Models\Http\Response;
use DateTime;

/**
 * EBICS client representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
interface EbicsClientInterface
{
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
     * @param DateTime|null $dateTime current date
     *
     * @return Response
     */
    public function INI(DateTime $dateTime = null): Response;

    /**
     * Make HIA request.
     * Send to the bank public signatures of authentication (X002) and encryption (E002).
     * Prepare E002 and X002 user signatures for KeyRing.
     *
     * @param DateTime|null $dateTime current date
     *
     * @return Response
     */
    public function HIA(DateTime $dateTime = null): Response;

    /**
     * Retrieve the Bank public signatures authentication (X002) and encryption (E002).
     * Decrypt OrderData.
     * Prepare E002 and X002 bank signatures for KeyRing.
     *
     * @param DateTime|null $dateTime current date
     *
     * @return Response
     */
    public function HPB(DateTime $dateTime = null): Response;

    /**
     * Retrieve the bank server parameters.
     * Send self::transferReceipt() after transaction finished.
     *
     * @param DateTime|null $dateTime
     *
     * @return Response
     */
    public function HPD(DateTime $dateTime = null): Response;

    /**
     * Retrieve customer's customer and subscriber information.
     * Send self::transferReceipt() after transaction finished.
     *
     * @param DateTime|null $dateTime
     *
     * @return Response
     */
    public function HKD(DateTime $dateTime = null): Response;

    /**
     * Retrieve subscriber's customer and subscriber information.
     * Send self::transferReceipt() after transaction finished.
     *
     * @param DateTime|null $dateTime
     *
     * @return Response
     */
    public function HTD(DateTime $dateTime = null): Response;

    /**
     * Retrieve subscriber's customer and subscriber information.
     * Send self::transferReceipt() after transaction finished.
     *
     * @param string $fileInfo
     * @param string $format
     * @param string $countryCode
     * @param DateTime|null $dateTime
     * @param DateTime|null $startDateTime
     * @param DateTime|null $endDateTime
     *
     * @return Response
     */
    public function FDL(
        string $fileInfo,
        string $format = 'plain',
        string $countryCode = 'FR',
        DateTime $dateTime = null,
        DateTime $startDateTime = null,
        DateTime $endDateTime = null
    ): Response;

    /**
     * Retrieve  Bank available order types.
     * Send self::transferReceipt() after transaction finished.
     *
     * @param DateTime|null $dateTime current date
     *
     * @return Response
     */
    public function HAA(DateTime $dateTime = null): Response;

    /**
     * Downloads the interim transaction report in SWIFT format (MT942).
     * Send self::transferReceipt() after transaction finished.
     *
     * @param DateTime|null $dateTime current date
     * @param DateTime|null $startDateTime the start date of requested transactions
     * @param DateTime|null $endDateTime the end date of requested transactions
     *
     * @return Response
     */
    public function VMK(
        DateTime $dateTime = null,
        DateTime $startDateTime = null,
        DateTime $endDateTime = null
    ): Response;

    /**
     * Retrieve the bank account statement.
     * Send self::transferReceipt() after transaction finished.
     *
     * @param DateTime|null $dateTime
     * @param DateTime|null $startDateTime the start date of requested transactions
     * @param DateTime|null $endDateTime the end date of requested transactions
     *
     * @return Response
     */
    public function STA(
        DateTime $dateTime = null,
        DateTime $startDateTime = null,
        DateTime $endDateTime = null
    ): Response;

    /**
     * Retrieve the bank account statement in Camt.053 format.
     * Send self::transferReceipt() after transaction finished.
     *
     * @param DateTime|null $dateTime
     * @param DateTime|null $startDateTime the start date of requested transactions
     * @param DateTime|null $endDateTime the end date of requested transactions
     *
     * @return Response
     */
    // @codingStandardsIgnoreStart
    public function C53(
        DateTime $dateTime = null,
        DateTime $startDateTime = null,
        DateTime $endDateTime = null
    ): Response;
    // @codingStandardsIgnoreEnd
    /**
     * Another way to retrieve the bank account statement in Camt.053 format (i.e Switzerland financial services).
     * Send self::transferReceipt() after transaction finished.
     *
     * @param DateTime|null $dateTime
     * @param DateTime|null $startDateTime the start date of requested transactions
     * @param DateTime|null $endDateTime the end date of requested transactions
     *
     * @return Response
     */
    // @codingStandardsIgnoreStart
    public function Z53(
        DateTime $dateTime = null,
        DateTime $startDateTime = null,
        DateTime $endDateTime = null
    ): Response;
    // @codingStandardsIgnoreEnd

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
     * Set certificate X509 Generator for French bank.
     *
     * @param X509GeneratorInterface|null $x509Generator
     */
    public function setX509Generator(X509GeneratorInterface $x509Generator = null): void;
}

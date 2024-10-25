<?php

namespace AndrewSvirin\Ebics\Contracts;

use AndrewSvirin\Ebics\Contexts\BTDContext;
use AndrewSvirin\Ebics\Contexts\BTUContext;
use AndrewSvirin\Ebics\Contexts\FULContext;
use AndrewSvirin\Ebics\Contexts\HVDContext;
use AndrewSvirin\Ebics\Contexts\HVEContext;
use AndrewSvirin\Ebics\Contexts\HVTContext;
use AndrewSvirin\Ebics\Handlers\ResponseHandler;
use AndrewSvirin\Ebics\Models\DownloadOrderResult;
use AndrewSvirin\Ebics\Models\Http\Response;
use AndrewSvirin\Ebics\Models\InitializationOrderResult;
use AndrewSvirin\Ebics\Models\UploadOrderResult;
use DateTimeInterface;

/**
 * EBICS client representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
interface EbicsClientInterface
{
    public const FILE_PARSER_FORMAT_TEXT = 'text';
    public const FILE_PARSER_FORMAT_XML = 'xml';
    public const FILE_PARSER_FORMAT_XML_FILES = 'xml_files';
    public const FILE_PARSER_FORMAT_ZIP_FILES = 'zip_files';

    public const COUNTRY_CODE_DE = 'DE';
    public const COUNTRY_CODE_FR = 'FR';
    public const COUNTRY_CODE_CH = 'CH';

    /**
     * Create user signatures A, E and X on first launch.
     */
    public function createUserSignatures(): void;

    /**
     * Download supported protocol versions for the Bank.
     *
     * @return Response
     */
    public function HEV(): Response;

    /**
     * Make INI request.
     * Send to the bank public signature of signature A00X.
     * Prepare A00X signature for Keyring.
     *
     * @param DateTimeInterface|null $dateTime current date
     *
     * @return Response
     */
    public function INI(DateTimeInterface $dateTime = null): Response;

    /**
     * Make HIA request.
     * Send to the bank public signatures of authentication (X002) and encryption (E002).
     * Prepare E002 and X002 user signatures for Keyring.
     *
     * @param DateTimeInterface|null $dateTime current date
     *
     * @return Response
     */
    public function HIA(DateTimeInterface $dateTime = null): Response;

    /**
     * Make H3K request.
     * Send to the bank public signatures of signature (A00X), authentication (X002) and encryption (E002).
     * Prepare A00X, E002 and X002 user signatures for Keyring.
     *
     * @param DateTimeInterface|null $dateTime current date
     *
     * @return Response
     */
    // @codingStandardsIgnoreStart
    public function H3K(DateTimeInterface $dateTime = null): Response;
    // @codingStandardsIgnoreEnd

    /**
     * Download the Bank public signatures authentication (X002) and encryption (E002).
     * Prepare E002 and X002 bank signatures for Keyring.
     *
     * @param DateTimeInterface|null $dateTime current date
     *
     * @return InitializationOrderResult
     */
    public function HPB(DateTimeInterface $dateTime = null): InitializationOrderResult;

    /**
     * Suspend activated Keyring.
     *
     * @param DateTimeInterface|null $dateTime
     *
     * @return UploadOrderResult
     */
    public function SPR(DateTimeInterface $dateTime = null): UploadOrderResult;

    /**
     * Download request files of any BTF structure.
     *
     * @param BTDContext $btfContext
     * @param DateTimeInterface|null $dateTime
     * @param DateTimeInterface|null $startDateTime
     * @param DateTimeInterface|null $endDateTime
     *
     * @return DownloadOrderResult
     */
    public function BTD(
        BTDContext $btfContext,
        DateTimeInterface $dateTime = null,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null
    ): DownloadOrderResult;

    /**
     * Upload the files to the bank.
     */
    public function BTU(BTUContext $btuContext, DateTimeInterface $dateTime = null): UploadOrderResult;

    /**
     * Download the bank server parameters.
     *
     * @param DateTimeInterface|null $dateTime
     *
     * @return DownloadOrderResult
     */
    public function HPD(DateTimeInterface $dateTime = null): DownloadOrderResult;

    /**
     * Download customer's customer and subscriber information.
     *
     * @param DateTimeInterface|null $dateTime
     *
     * @return DownloadOrderResult
     */
    public function HKD(DateTimeInterface $dateTime = null): DownloadOrderResult;

    /**
     * Download subscriber's customer and subscriber information.
     *
     * @param DateTimeInterface|null $dateTime
     *
     * @return DownloadOrderResult
     */
    public function HTD(DateTimeInterface $dateTime = null): DownloadOrderResult;

    /**
     * Download transaction status.
     *
     * @param DateTimeInterface|null $dateTime
     * @param DateTimeInterface|null $startDateTime
     * @param DateTimeInterface|null $endDateTime
     *
     * @return DownloadOrderResult
     */
    public function PTK(
        DateTimeInterface $dateTime = null,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null
    ): DownloadOrderResult;

    /**
     * Download Bank available order types.
     *
     * @param DateTimeInterface|null $dateTime current date
     *
     * @return DownloadOrderResult
     */
    public function HAA(DateTimeInterface $dateTime = null): DownloadOrderResult;

    /**
     * Download the interim transaction report in SWIFT format (MT942).
     * OrderType:BTD, Service Name:STM, Scope:BIL, Container:, MsgName:mt942
     *
     * @param DateTimeInterface|null $dateTime current date
     * @param DateTimeInterface|null $startDateTime the start date of requested transactions
     * @param DateTimeInterface|null $endDateTime the end date of requested transactions
     *
     * @return DownloadOrderResult
     */
    public function VMK(
        DateTimeInterface $dateTime = null,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null
    ): DownloadOrderResult;

    /**
     * Download the bank account statement.
     * OrderType:BTD, Service Name:EOP, Scope:BIL, Container:, MsgName:mt940
     *
     * @param DateTimeInterface|null $dateTime
     * @param DateTimeInterface|null $startDateTime the start date of requested transactions
     * @param DateTimeInterface|null $endDateTime the end date of requested transactions
     *
     * @return DownloadOrderResult
     */
    public function STA(
        DateTimeInterface $dateTime = null,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null
    ): DownloadOrderResult;

    /**
     * Download the bank account report in Camt.052 format.
     * OrderType:BTD, Service Name:STM, Scope:BIL, Container:ZIP, MsgName:camt.052
     *
     * @param DateTimeInterface|null $dateTime
     * @param DateTimeInterface|null $startDateTime the start date of requested transactions
     * @param DateTimeInterface|null $endDateTime the end date of requested transactions
     *
     * @return DownloadOrderResult
     */
    // @codingStandardsIgnoreStart
    public function C52(
        DateTimeInterface $dateTime = null,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null
    ): DownloadOrderResult;
    // @codingStandardsIgnoreEnd

    /**
     * Download the bank account statement in Camt.053 format.
     * OrderType:BTD, Service Name:EOP, Scope:BIL, Container:ZIP, MsgName:camt.053
     *
     * @param DateTimeInterface|null $dateTime
     * @param DateTimeInterface|null $startDateTime the start date of requested transactions
     * @param DateTimeInterface|null $endDateTime the end date of requested transactions
     *
     * @return DownloadOrderResult
     */
    // @codingStandardsIgnoreStart
    public function C53(
        DateTimeInterface $dateTime = null,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null
    ): DownloadOrderResult;
    // @codingStandardsIgnoreEnd

    /**
     * Download Debit Credit Notification (DTI).
     * OrderType:BTD, Service Name:STM, Scope:BIL, Container:ZIP, MsgName:camt.054
     *
     * @param DateTimeInterface|null $dateTime
     * @param DateTimeInterface|null $startDateTime the start date of requested transactions
     * @param DateTimeInterface|null $endDateTime the end date of requested transactions
     *
     * @return DownloadOrderResult
     */
    // @codingStandardsIgnoreStart
    public function C54(
        DateTimeInterface $dateTime = null,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null
    ): DownloadOrderResult;
    // @codingStandardsIgnoreEnd

    /**
     * Download the bank account report in Camt.052 format (i.e Switzerland financial services).
     * OrderType:BTD, Service Name:STM, Scope:CH, Container:ZIP, MsgName:camt.052,Version:04
     *
     * @param DateTimeInterface|null $dateTime
     * @param DateTimeInterface|null $startDateTime the start date of requested transactions
     * @param DateTimeInterface|null $endDateTime the end date of requested transactions
     *
     * @return DownloadOrderResult
     */
    // @codingStandardsIgnoreStart
    public function Z52(
        DateTimeInterface $dateTime = null,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null
    ): DownloadOrderResult;
    // @codingStandardsIgnoreEnd

    /**
     * Download the bank account statement in Camt.053 format (i.e Switzerland financial services).
     * OrderType:BTD, Service Name:EOP, Scope:CH, Container:ZIP, MsgName:camt.053,Version:04
     *
     * @param DateTimeInterface|null $dateTime
     * @param DateTimeInterface|null $startDateTime the start date of requested transactions
     * @param DateTimeInterface|null $endDateTime the end date of requested transactions
     *
     * @return DownloadOrderResult
     */
    // @codingStandardsIgnoreStart
    public function Z53(
        DateTimeInterface $dateTime = null,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null
    ): DownloadOrderResult;
    // @codingStandardsIgnoreEnd

    /**
     * Download the bank account statement in Camt.054 format (i.e available in Switzerland).
     * OrderType:BTD, Service Name:REP, Scope:CH, Container:ZIP, MsgName:camt.054,Version:04
     *
     * @param DateTimeInterface|null $dateTime
     * @param DateTimeInterface|null $startDateTime the start date of requested transactions
     * @param DateTimeInterface|null $endDateTime the end date of requested transactions
     *
     * @return DownloadOrderResult
     */
    // @codingStandardsIgnoreStart
    public function Z54(
        DateTimeInterface $dateTime = null,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null
    ): DownloadOrderResult;
    // @codingStandardsIgnoreEnd

    /**
     * Download Order/Payment Status report.
     * OrderType:BTD, Service Name:PSR, Scope:BIL, Container:ZIP, MsgName:pain.002
     *
     * @param DateTimeInterface|null $dateTime
     * @param DateTimeInterface|null $startDateTime
     * @param DateTimeInterface|null $endDateTime
     *
     * @return DownloadOrderResult
     */
    public function ZSR(
        DateTimeInterface $dateTime = null,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null
    ): DownloadOrderResult;

    /**
     * Download account information as PDF-file.
     *
     * @param DateTimeInterface|null $dateTime
     * @param DateTimeInterface|null $startDateTime
     * @param DateTimeInterface|null $endDateTime
     *
     * @return DownloadOrderResult
     */
    public function XEK(
        DateTimeInterface $dateTime = null,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null
    ): DownloadOrderResult;

    /**
     * Download subscriber's customer and subscriber information.
     *
     * @param string $fileFormat Format of response. ex 'pain.001.001.03.sct'
     * @param string $parserFormat How to handle response.
     * @param string $countryCode Country code (ISO 3166-1 alpha-2) (max 2 char)
     * @param DateTimeInterface|null $dateTime
     * @param DateTimeInterface|null $startDateTime
     * @param DateTimeInterface|null $endDateTime
     * @param callable|null $storeClosure Custom closure to handle download acknowledge.
     *
     * @return DownloadOrderResult
     */
    public function FDL(
        string $fileFormat,
        string $parserFormat = self::FILE_PARSER_FORMAT_TEXT,
        string $countryCode = self::COUNTRY_CODE_DE,
        DateTimeInterface $dateTime = null,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null,
        $storeClosure = null
    ): DownloadOrderResult;

    /**
     * Standard order type for submitting the files to the bank. Using this order type ensures a
     * transparent transfer of files of any format.
     *
     * @param string $fileFormat Format of request ex 'pain.001.001.03.sct'
     * @param OrderDataInterface $orderData File to be uploaded.
     * @param FULContext $fulContext Order attributes.
     * @param DateTimeInterface|null $dateTime
     * @param bool $withES EBICS T or TS mode when false. (the file contains both order data and signature(s))
     *
     * @return UploadOrderResult
     */
    public function FUL(
        string $fileFormat,
        OrderDataInterface $orderData,
        FULContext $fulContext,
        DateTimeInterface $dateTime = null,
        bool $withES = true
    ): UploadOrderResult;

    /**
     * Upload initiation of the credit transfer per SEPA.
     * specification set by the European Payment Council or Die Deutsche Kreditwirtschaft (DK (German)).
     * CCT is an upload order type that uses the protocol version H00X.
     * FileFormat pain.001.001.03
     * OrderType:BTU, Service Name:SCT, Scope:DE, Container:, MsgName:pain.001
     *
     * @param OrderDataInterface $orderData
     * @param DateTimeInterface|null $dateTime
     * @param bool $withES EBICS T or TS mode when false. (the file contains both order data and signature(s))
     *
     * @return UploadOrderResult
     */
    public function CCT(
        OrderDataInterface $orderData,
        DateTimeInterface $dateTime = null,
        bool $withES = true
    ): UploadOrderResult;

    /**
     * Upload initiation of the direct debit transaction.
     * The CDD order type uses the protocol version H00X.
     * FileFormat pain.008.001.02
     * OrderType:BTU, Service Name:SDD, Scope:SDD,Service Option:COR Container:, MsgName:pain.008
     *
     * @param OrderDataInterface $orderData
     * @param DateTimeInterface|null $dateTime
     * @param bool $withES EBICS T or TS mode when false. (the file contains both order data and signature(s))
     *
     * @return UploadOrderResult
     */
    public function CDD(
        OrderDataInterface $orderData,
        DateTimeInterface $dateTime = null,
        bool $withES = true
    ): UploadOrderResult;

    /**
     * Upload initiation of the direct debit transaction for business.
     * The CDB order type uses the protocol version H00X.
     * FileFormat pain.008.001.02
     * OrderType:BTU, Service Name:SDD, Scope:SDD,Service Option:COR Container:, MsgName:pain.008
     *
     * @param OrderDataInterface $orderData
     * @param DateTimeInterface|null $dateTime
     * @param bool $withES EBICS T or TS mode when false. (the file contains both order data and signature(s))
     *
     * @return UploadOrderResult
     */
    public function CDB(
        OrderDataInterface $orderData,
        DateTimeInterface $dateTime = null,
        bool $withES = true
    ): UploadOrderResult;

    /**
     * Upload initiation of the instant credit transfer per SEPA.
     *
     * @param OrderDataInterface $orderData
     * @param DateTimeInterface|null $dateTime
     * @param bool $withES EBICS T or TS mode when false. (the file contains both order data and signature(s))
     *
     * @return UploadOrderResult
     */
    public function CIP(
        OrderDataInterface $orderData,
        DateTimeInterface $dateTime = null,
        bool $withES = true
    ): UploadOrderResult;

    /**
     * Upload initiation credit transfer per Swiss Payments specification set by Six banking services.
     * XE2 is an upload order type that uses the protocol version H00X.
     * FileFormat pain.001.001.03.ch.02
     * OrderType:BTU, Service Name:MCT, Scope:CH,Service Option:COR Container:, MsgName:pain.001,Version: 03
     *
     * @param OrderDataInterface $orderData
     * @param DateTimeInterface|null $dateTime
     * @param bool $withES EBICS T or TS mode when false. (the file contains both order data and signature(s))
     *
     * @return UploadOrderResult
     */
    public function XE2(
        OrderDataInterface $orderData,
        DateTimeInterface $dateTime = null,
        bool $withES = true
    ): UploadOrderResult;

    /**
     * Upload SEPA Direct Debit Initiation, CH definitions, CORE.
     * FileFormat pain.008.001.03.ch.02
     * OrderType:BTU, Service Name:SDD, Scope:CH,Service Option:COR Container:, MsgName:pain.008,Version: 02
     *
     * @param OrderDataInterface $orderData
     * @param DateTimeInterface|null $dateTime
     * @param bool $withES EBICS T or TS mode when false. (the file contains both order data and signature(s))
     *
     * @return UploadOrderResult
     */
    public function XE3(
        OrderDataInterface $orderData,
        DateTimeInterface $dateTime = null,
        bool $withES = true
    ): UploadOrderResult;

    /**
     * Upload Credit transfer CGI (SEPA & non SEPA).
     * OrderType:BTU, Service Name:MCT, Scope:BIL, Container:, MsgName:pain.001
     *
     * @param OrderDataInterface $orderData
     * @param DateTimeInterface|null $dateTime
     *
     * @return UploadOrderResult
     */
    public function YCT(OrderDataInterface $orderData, DateTimeInterface $dateTime = null): UploadOrderResult;

    /**
     * Download List the orders for which the user is authorized as a signatory.
     *
     * @param DateTimeInterface|null $dateTime
     *
     * @return DownloadOrderResult
     */
    public function HVU(DateTimeInterface $dateTime = null): DownloadOrderResult;

    /**
     * Download VEU overview with additional information.
     *
     * @param DateTimeInterface|null $dateTime
     *
     * @return DownloadOrderResult
     */
    public function HVZ(DateTimeInterface $dateTime = null): DownloadOrderResult;

    /**
     * Add a VEU signature for order.
     *
     * @param HVEContext $hveContext
     * @param DateTimeInterface|null $dateTime
     *
     * @return UploadOrderResult
     */
    public function HVE(HVEContext $hveContext, DateTimeInterface $dateTime = null): UploadOrderResult;

    /**
     * Download the state of a VEU order.
     *
     * @param HVDContext $hvdContext
     * @param DateTimeInterface|null $dateTime
     *
     * @return DownloadOrderResult
     */
    public function HVD(HVDContext $hvdContext, DateTimeInterface $dateTime = null): DownloadOrderResult;

    /**
     * Download detailed information about an order from VEU processing for which the user is authorized as a signatory.
     *
     * @param HVTContext $hvtContext
     * @param DateTimeInterface|null $dateTime
     *
     * @return DownloadOrderResult
     */
    public function HVT(HVTContext $hvtContext, DateTimeInterface $dateTime = null): DownloadOrderResult;

    /**
     * Set http client to subset later in the project.
     *
     * @param HttpClientInterface $httpClient
     */
    public function setHttpClient(HttpClientInterface $httpClient): void;

    /**
     * Get response handler for manual process response.
     *
     * @return ResponseHandler
     */
    public function getResponseHandler(): ResponseHandler;

    /**
     * Check keyring is valid.
     *
     * @return bool
     */
    public function checkKeyring(): bool;

    /**
     * Change password for keyring.
     *
     * @param string $newPassword
     *
     * @return void
     */
    public function changeKeyringPassword(string $newPassword): void;
}

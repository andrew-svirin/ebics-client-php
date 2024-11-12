<?php

namespace AndrewSvirin\Ebics\Contracts;

use AndrewSvirin\Ebics\Contexts\BTDContext;
use AndrewSvirin\Ebics\Contexts\BTUContext;
use AndrewSvirin\Ebics\Contexts\FDLContext;
use AndrewSvirin\Ebics\Contexts\FULContext;
use AndrewSvirin\Ebics\Contexts\HVDContext;
use AndrewSvirin\Ebics\Contexts\HVEContext;
use AndrewSvirin\Ebics\Contexts\HVTContext;
use AndrewSvirin\Ebics\Contexts\RequestContext;
use AndrewSvirin\Ebics\Handlers\ResponseHandler;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\DownloadOrderResult;
use AndrewSvirin\Ebics\Models\Http\Response;
use AndrewSvirin\Ebics\Models\InitializationOrderResult;
use AndrewSvirin\Ebics\Models\Keyring;
use AndrewSvirin\Ebics\Models\UploadOrderResult;
use AndrewSvirin\Ebics\Models\User;
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

    public const COUNTRY_CODE_EU = 'EU';
    public const COUNTRY_CODE_DE = 'DE';
    public const COUNTRY_CODE_FR = 'FR';
    public const COUNTRY_CODE_CH = 'CH';

    /**
     * Create user signatures A, E and X on first launch.
     */
    public function createUserSignatures(string $aVersion): void;

    /**
     * Download supported protocol versions for the Bank.
     *
     * @return Response
     */
    public function HEV(): Response;

    /**
     * Make INI request.
     * Send to the bank public signature of signature A005|A006.
     *
     * @param RequestContext|null $context
     *
     * @return Response
     */
    public function INI(RequestContext $context = null): Response;

    /**
     * Make HIA request.
     * Send to the bank public signatures of authentication (X002) and encryption (E002).
     *
     * @param RequestContext|null $context
     *
     * @return Response
     */
    public function HIA(RequestContext $context = null): Response;

    /**
     * Make H3K request.
     * Send to the bank public signatures of signature (A005|A006), authentication (X002) and encryption (E002).
     *
     * @param RequestContext|null $context
     *
     * @return Response
     */
    public function H3K(RequestContext $context = null): Response;

    /**
     * Download the Bank public signatures authentication (X002) and encryption (E002).
     * Prepare E002 and X002 bank signatures for Keyring.
     *
     * @param RequestContext|null $context
     *
     * @return InitializationOrderResult
     */
    public function HPB(RequestContext $context = null): InitializationOrderResult;

    /**
     * Suspend activated Keyring.
     *
     * @param RequestContext|null $context
     *
     * @return UploadOrderResult
     */
    public function SPR(RequestContext $context = null): UploadOrderResult;

    /**
     * Download the bank server parameters.
     *
     * @param RequestContext|null $context
     *
     * @return DownloadOrderResult
     */
    public function HPD(RequestContext $context = null): DownloadOrderResult;

    /**
     * Download customer's customer and subscriber information.
     *
     * @param RequestContext|null $context
     *
     * @return DownloadOrderResult
     */
    public function HKD(RequestContext $context = null): DownloadOrderResult;

    /**
     * Download subscriber's customer and subscriber information.
     *
     * @param RequestContext|null $context
     *
     * @return DownloadOrderResult
     */
    public function HTD(RequestContext $context = null): DownloadOrderResult;

    /**
     * Download Bank available order types.
     *
     * @param RequestContext|null $context
     *
     * @return DownloadOrderResult
     */
    public function HAA(RequestContext $context = null): DownloadOrderResult;

    /**
     * Download transaction status.
     *
     * @param DateTimeInterface|null $startDateTime the start date of requested transactions
     * @param DateTimeInterface|null $endDateTime the end date of requested transactions
     * @param RequestContext|null $context
     *
     * @return DownloadOrderResult
     */
    public function PTK(
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null,
        RequestContext $context = null
    ): DownloadOrderResult;

    /**
     * Download the interim transaction report in SWIFT format (MT942).
     *
     * @param DateTimeInterface|null $startDateTime the start date of requested transactions
     * @param DateTimeInterface|null $endDateTime the end date of requested transactions
     * @param RequestContext|null $context
     *
     * @return DownloadOrderResult
     */
    public function VMK(
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null,
        RequestContext $context = null
    ): DownloadOrderResult;

    /**
     * Download the bank account statement.
     *
     * @param DateTimeInterface|null $startDateTime the start date of requested transactions
     * @param DateTimeInterface|null $endDateTime the end date of requested transactions
     * @param RequestContext|null $context
     *
     * @return DownloadOrderResult
     */
    public function STA(
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null,
        RequestContext $context = null
    ): DownloadOrderResult;

    /**
     * Download the bank account report in camt.052 format.
     *
     * @param DateTimeInterface|null $startDateTime the start date of requested transactions
     * @param DateTimeInterface|null $endDateTime the end date of requested transactions
     * @param RequestContext|null $context
     *
     * @return DownloadOrderResult
     */
    public function C52(
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null,
        RequestContext $context = null
    ): DownloadOrderResult;

    /**
     * Download the bank account statement in camt.053 format.
     *
     * @param DateTimeInterface|null $startDateTime the start date of requested transactions
     * @param DateTimeInterface|null $endDateTime the end date of requested transactions
     * @param RequestContext|null $context
     *
     * @return DownloadOrderResult
     */
    public function C53(
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null,
        RequestContext $context = null
    ): DownloadOrderResult;

    /**
     * Download Debit Credit Notification (DTI) in camt.053 format.
     *
     * @param DateTimeInterface|null $startDateTime the start date of requested transactions
     * @param DateTimeInterface|null $endDateTime the end date of requested transactions
     * @param RequestContext|null $context
     *
     * @return DownloadOrderResult
     */
    public function C54(
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null,
        RequestContext $context = null
    ): DownloadOrderResult;

    /**
     * Download the bank account report in camt.052 format (i.e Switzerland financial services).
     *
     * @param DateTimeInterface|null $startDateTime the start date of requested transactions
     * @param DateTimeInterface|null $endDateTime the end date of requested transactions
     * @param RequestContext|null $context
     *
     * @return DownloadOrderResult
     */
    public function Z52(
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null,
        RequestContext $context = null
    ): DownloadOrderResult;

    /**
     * Download the bank account statement in camt.053 format (i.e Switzerland financial services).
     *
     * @param DateTimeInterface|null $startDateTime the start date of requested transactions
     * @param DateTimeInterface|null $endDateTime the end date of requested transactions
     * @param RequestContext|null $context
     *
     * @return DownloadOrderResult
     */
    public function Z53(
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null,
        RequestContext $context = null
    ): DownloadOrderResult;

    /**
     * Download the bank account statement in camt.054 format (i.e available in Switzerland).
     *
     * @param DateTimeInterface|null $startDateTime the start date of requested transactions
     * @param DateTimeInterface|null $endDateTime the end date of requested transactions
     * @param RequestContext|null $context
     *
     * @return DownloadOrderResult
     */
    public function Z54(
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null,
        RequestContext $context = null
    ): DownloadOrderResult;

    /**
     * Download Order/Payment Status report.
     *
     * @param DateTimeInterface|null $startDateTime the start date of requested transactions
     * @param DateTimeInterface|null $endDateTime the end date of requested transactions
     * @param RequestContext|null $context
     *
     * @return DownloadOrderResult
     */
    public function ZSR(
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null,
        RequestContext $context = null
    ): DownloadOrderResult;

    /**
     * Download account information as PDF-file.
     *
     * @param DateTimeInterface|null $startDateTime the start date of requested transactions
     * @param DateTimeInterface|null $endDateTime the end date of requested transactions
     * @param RequestContext|null $context
     *
     * @return DownloadOrderResult
     */
    public function XEK(
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null,
        RequestContext $context = null
    ): DownloadOrderResult;

    /**
     * Download request files of any BTF structure.
     *
     * @param BTDContext $btdContext
     * @param DateTimeInterface|null $startDateTime the start date of requested transactions
     * @param DateTimeInterface|null $endDateTime the end date of requested transactions
     * @param RequestContext|null $context
     *
     * @return DownloadOrderResult
     */
    public function BTD(
        BTDContext $btdContext,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null,
        RequestContext $context = null
    ): DownloadOrderResult;

    /**
     * Upload the files to the bank of any BTF structure.
     *
     * @param BTUContext $btuContext
     * @param OrderDataInterface $orderData
     * @param RequestContext|null $context
     *
     * @return UploadOrderResult
     */
    public function BTU(
        BTUContext $btuContext,
        OrderDataInterface $orderData,
        RequestContext $context = null
    ): UploadOrderResult;

    /**
     * Download subscriber's customer and subscriber information.
     *
     * @param FDLContext $fdlContext
     * @param DateTimeInterface|null $startDateTime
     * @param DateTimeInterface|null $endDateTime
     * @param RequestContext|null $context
     *
     * @return DownloadOrderResult
     */
    public function FDL(
        FDLContext $fdlContext,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null,
        RequestContext $context = null
    ): DownloadOrderResult;

    /**
     * Standard order type for submitting the files to the bank. Using this order type ensures a
     * transparent transfer of files of any format.
     *
     * @param FULContext $fulContext
     * @param OrderDataInterface $orderData
     * @param RequestContext|null $context
     *
     * @return UploadOrderResult
     */
    public function FUL(
        FULContext $fulContext,
        OrderDataInterface $orderData,
        RequestContext $context = null
    ): UploadOrderResult;

    /**
     * Upload initiation of the credit transfer per SEPA.
     * specification set by the European Payment Council or Die Deutsche Kreditwirtschaft (DK (German)).
     * CCT is an upload order type that uses the protocol version H00X.
     * FileFormat pain.001.001.03
     * OrderType:BTU, Service Name:SCT, Scope:DE, Container:, MsgName:pain.001
     *
     * @param OrderDataInterface $orderData
     * @param RequestContext|null $context
     *
     * @return UploadOrderResult
     */
    public function CCT(OrderDataInterface $orderData, RequestContext $context = null): UploadOrderResult;

    /**
     * Upload initiation of the direct debit transaction.
     * The CDD order type uses the protocol version H00X.
     * FileFormat pain.008.001.02
     * OrderType:BTU, Service Name:SDD, Scope:SDD,Service Option:COR Container:, MsgName:pain.008
     *
     * @param OrderDataInterface $orderData
     * @param RequestContext|null $context
     *
     * @return UploadOrderResult
     */
    public function CDD(OrderDataInterface $orderData, RequestContext $context = null): UploadOrderResult;

    /**
     * Upload initiation of the direct debit transaction for business.
     * The CDB order type uses the protocol version H00X.
     * FileFormat pain.008.001.02
     * OrderType:BTU, Service Name:SDD, Scope:SDD,Service Option:COR Container:, MsgName:pain.008
     *
     * @param OrderDataInterface $orderData
     * @param RequestContext|null $context
     *
     * @return UploadOrderResult
     */
    public function CDB(OrderDataInterface $orderData, RequestContext $context = null): UploadOrderResult;

    /**
     * Upload initiation of the instant credit transfer per SEPA.
     *
     * @param OrderDataInterface $orderData
     * @param RequestContext|null $context
     *
     * @return UploadOrderResult
     */
    public function CIP(OrderDataInterface $orderData, RequestContext $context = null): UploadOrderResult;

    /**
     * Upload initiation credit transfer per Swiss Payments specification set by Six banking services.
     * XE2 is an upload order type that uses the protocol version H00X.
     * FileFormat pain.001.001.03.ch.02
     * OrderType:BTU, Service Name:MCT, Scope:CH,Service Option:COR Container:, MsgName:pain.001,Version: 03
     *
     * @param OrderDataInterface $orderData
     * @param RequestContext|null $context
     *
     * @return UploadOrderResult
     */
    public function XE2(OrderDataInterface $orderData, RequestContext $context = null): UploadOrderResult;

    /**
     * Upload SEPA Direct Debit Initiation, CH definitions, CORE.
     * FileFormat pain.008.001.03.ch.02
     * OrderType:BTU, Service Name:SDD, Scope:CH,Service Option:COR Container:, MsgName:pain.008,Version: 02
     *
     * @param OrderDataInterface $orderData
     * @param RequestContext|null $context
     *
     * @return UploadOrderResult
     */
    public function XE3(OrderDataInterface $orderData, RequestContext $context = null): UploadOrderResult;

    /**
     * Upload Credit transfer CGI (SEPA & non SEPA).
     * OrderType:BTU, Service Name:MCT, Scope:BIL, Container:, MsgName:pain.001
     *
     * @param OrderDataInterface $orderData
     * @param RequestContext|null $context
     *
     * @return UploadOrderResult
     */
    public function YCT(OrderDataInterface $orderData, RequestContext $context = null): UploadOrderResult;

    /**
     * Download List the orders for which the user is authorized as a signatory.
     *
     * @param RequestContext|null $context
     *
     * @return DownloadOrderResult
     */
    public function HVU(RequestContext $context = null): DownloadOrderResult;

    /**
     * Download VEU overview with additional information.
     *
     * @param RequestContext|null $context
     *
     * @return DownloadOrderResult
     */
    public function HVZ(RequestContext $context = null): DownloadOrderResult;

    /**
     * Add a VEU signature for order.
     *
     * @param HVEContext $hveContext
     * @param RequestContext|null $context
     *
     * @return UploadOrderResult
     */
    public function HVE(HVEContext $hveContext, RequestContext $context = null): UploadOrderResult;

    /**
     * Download the state of a VEU order.
     *
     * @param HVDContext $hvdContext
     * @param RequestContext|null $context
     *
     * @return DownloadOrderResult
     */
    public function HVD(HVDContext $hvdContext, RequestContext $context = null): DownloadOrderResult;

    /**
     * Download detailed information about an order from VEU processing for which the user is authorized as a signatory.
     *
     * @param HVTContext $hvtContext
     * @param RequestContext|null $context
     *
     * @return DownloadOrderResult
     */
    public function HVT(HVTContext $hvtContext, RequestContext $context = null): DownloadOrderResult;

    /**
     * Get Keyring.
     *
     * @return Keyring
     */
    public function getKeyring(): Keyring;

    /**
     * Get Bank.
     *
     * @return Bank
     */
    public function getBank(): Bank;

    /**
     * Get User.
     *
     * @return User
     */
    public function getUser(): User;

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

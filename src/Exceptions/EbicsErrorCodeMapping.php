<?php

namespace EbicsApi\Ebics\Exceptions;

/**
 * Mapping class between error code and exception classes. @see \EbicsApi\Ebics\Factories\EbicsExceptionFactory
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
abstract class EbicsErrorCodeMapping
{
    /**
     * @var class-string<EbicsResponseException>[]
     */
    public static $mapping = [
        '011000' => DownloadPostprocessDoneException::class,
        '011001' => DownloadPostprocessSkippedException::class,
        '011101' => TxSegmentNumberUnderrunException::class,
        '031001' => OrderParamsIgnoredException::class,
        '061001' => AuthenticationFailedException::class,
        '061002' => InvalidRequestException::class,
        '061099' => InternalErrorException::class,
        '061101' => TxRecoverySyncException::class,
        '091002' => InvalidUserOrUserStateException::class,
        '091003' => UserUnknownException::class,
        '091004' => InvalidUserStateException::class,
        '091005' => InvalidOrderTypeException::class,
        '091006' => UnsupportedOrderTypeException::class,
        '091007' => DistributedSignatureAuthorisationFailedException::class,
        '091008' => BankPubkeyUpdateRequiredException::class,
        '091009' => SegmentSizeExceededException::class,
        '091010' => InvalidXmlException::class,
        '091011' => InvalidHostIdException::class,
        '091101' => TxUnknownTxidException::class,
        '091102' => TxAbortException::class,
        '091103' => TxMessageReplayException::class,
        '091104' => TxSegmentNumberExceededException::class,
        '091112' => InvalidOrderParamsException::class,
        '091113' => InvalidRequestContentException::class,
        '091117' => MaxOrderDataSizeExceededException::class,
        '091118' => MaxSegmentsExceededException::class,
        '091119' => MaxTransactionsExceededException::class,
        '091120' => PartnerIdMismatchException::class,
        '091121' => IncompatibleOrderAttributeException::class,
        '091219' => CertificatesValidationErrorException::class,
        '011301' => NoOnlineChecksException::class,
        '091001' => DownloadSignedOnlyException::class,
        '090003' => AuthorisationOrderTypeFailedException::class,
        '090004' => InvalidOrderDataFormatException::class,
        '090005' => NoDownloadDataAvailableException::class,
        '090006' => UnsupportedRequestForOrderInstanceException::class,
        '091105' => RecoveryNotSupportedException::class,
        '091111' => InvalidSignatureFileFormatException::class,
        '091114' => OrderidUnknownException::class,
        '091115' => OrderidAlreadyExistsException::class,
        '091116' => ProcessingErrorException::class,
        '091201' => KeymgmtUnsupportedVersionSignatureException::class,
        '091202' => KeymgmtUnsupportedVersionAuthenticationException::class,
        '091203' => KeymgmtUnsupportedVersionEncryptionException::class,
        '091204' => KeymgmtKeylengthErrorSignatureException::class,
        '091205' => KeymgmtKeylengthErrorAuthenticationException::class,
        '091206' => KeymgmtKeylengthErrorEncryptionException::class,
        '091207' => KeymgmtNoX509SupportException::class,
        '091208' => X509CertificateExpiredException::class,
        '091209' => X509CertificateNotValidYetException::class,
        '091210' => X509WrongKeyUsageException::class,
        '091211' => X509WrongAlgorithmException::class,
        '091212' => X509InvalidThumbprintException::class,
        '091213' => X509CtlInvalidException::class,
        '091214' => X509UnknownCertificateAuthorityException::class,
        '091215' => X509InvalidPolicyException::class,
        '091216' => X509InvalidBasicConstraintsException::class,
        '091217' => OnlyX509SupportException::class,
        '091218' => KeymgmtDuplicateKeyException::class,
        '091301' => SignatureVerificationFailedException::class,
        '091302' => AccountAuthorisationFailedException::class,
        '091303' => AmountCheckFailedException::class,
        '091304' => SignerUnknownException::class,
        '091305' => InvalidSignerStateException::class,
        '091306' => DuplicateSignatureException::class,
    ];

    /**
     * @param string $errorCode
     * @return class-string<EbicsResponseException>|null
     */
    public static function resolveClass(string $errorCode): ?string
    {
        return self::$mapping[$errorCode] ?? null;
    }
}

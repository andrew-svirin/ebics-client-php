<?php

declare(strict_types=1);

namespace AndrewSvirin\Ebics\Tests\Factories;

use AndrewSvirin\Ebics\Exceptions\AccountAuthorisationFailedException;
use AndrewSvirin\Ebics\Exceptions\AmountCheckFailedException;
use AndrewSvirin\Ebics\Exceptions\AuthenticationFailedException;
use AndrewSvirin\Ebics\Exceptions\AuthorisationOrderTypeFailedException;
use AndrewSvirin\Ebics\Exceptions\BankPubkeyUpdateRequiredException;
use AndrewSvirin\Ebics\Exceptions\CertificatesValidationErrorException;
use AndrewSvirin\Ebics\Exceptions\DistributedSignatureAuthorisationFailedException;
use AndrewSvirin\Ebics\Exceptions\DownloadPostprocessDoneException;
use AndrewSvirin\Ebics\Exceptions\DownloadPostprocessSkippedException;
use AndrewSvirin\Ebics\Exceptions\DownloadSignedOnlyException;
use AndrewSvirin\Ebics\Exceptions\DuplicateSignatureException;
use AndrewSvirin\Ebics\Exceptions\EbicsResponseException;
use AndrewSvirin\Ebics\Exceptions\IncompatibleOrderAttributeException;
use AndrewSvirin\Ebics\Exceptions\InternalErrorException;
use AndrewSvirin\Ebics\Exceptions\InvalidHostIdException;
use AndrewSvirin\Ebics\Exceptions\InvalidOrderDataFormatException;
use AndrewSvirin\Ebics\Exceptions\InvalidOrderParamsException;
use AndrewSvirin\Ebics\Exceptions\InvalidOrderTypeException;
use AndrewSvirin\Ebics\Exceptions\InvalidRequestContentException;
use AndrewSvirin\Ebics\Exceptions\InvalidRequestException;
use AndrewSvirin\Ebics\Exceptions\InvalidSignatureFileFormatException;
use AndrewSvirin\Ebics\Exceptions\InvalidSignerStateException;
use AndrewSvirin\Ebics\Exceptions\InvalidUserOrUserStateException;
use AndrewSvirin\Ebics\Exceptions\InvalidUserStateException;
use AndrewSvirin\Ebics\Exceptions\InvalidXmlException;
use AndrewSvirin\Ebics\Exceptions\KeymgmtDuplicateKeyException;
use AndrewSvirin\Ebics\Exceptions\KeymgmtKeylengthErrorAuthenticationException;
use AndrewSvirin\Ebics\Exceptions\KeymgmtKeylengthErrorEncryptionException;
use AndrewSvirin\Ebics\Exceptions\KeymgmtKeylengthErrorSignatureException;
use AndrewSvirin\Ebics\Exceptions\KeymgmtNoX509SupportException;
use AndrewSvirin\Ebics\Exceptions\KeymgmtUnsupportedVersionAuthenticationException;
use AndrewSvirin\Ebics\Exceptions\KeymgmtUnsupportedVersionEncryptionException;
use AndrewSvirin\Ebics\Exceptions\KeymgmtUnsupportedVersionSignatureException;
use AndrewSvirin\Ebics\Exceptions\MaxOrderDataSizeExceededException;
use AndrewSvirin\Ebics\Exceptions\MaxSegmentsExceededException;
use AndrewSvirin\Ebics\Exceptions\MaxTransactionsExceededException;
use AndrewSvirin\Ebics\Exceptions\NoDownloadDataAvailableException;
use AndrewSvirin\Ebics\Exceptions\NoOnlineChecksException;
use AndrewSvirin\Ebics\Exceptions\OnlyX509SupportException;
use AndrewSvirin\Ebics\Exceptions\OrderidAlreadyExistsException;
use AndrewSvirin\Ebics\Exceptions\OrderidUnknownException;
use AndrewSvirin\Ebics\Exceptions\OrderParamsIgnoredException;
use AndrewSvirin\Ebics\Exceptions\PartnerIdMismatchException;
use AndrewSvirin\Ebics\Exceptions\ProcessingErrorException;
use AndrewSvirin\Ebics\Exceptions\RecoveryNotSupportedException;
use AndrewSvirin\Ebics\Exceptions\SegmentSizeExceededException;
use AndrewSvirin\Ebics\Exceptions\SignatureVerificationFailedException;
use AndrewSvirin\Ebics\Exceptions\SignerUnknownException;
use AndrewSvirin\Ebics\Exceptions\TxAbortException;
use AndrewSvirin\Ebics\Exceptions\TxMessageReplayException;
use AndrewSvirin\Ebics\Exceptions\TxRecoverySyncException;
use AndrewSvirin\Ebics\Exceptions\TxSegmentNumberExceededException;
use AndrewSvirin\Ebics\Exceptions\TxSegmentNumberUnderrunException;
use AndrewSvirin\Ebics\Exceptions\TxUnknownTxidException;
use AndrewSvirin\Ebics\Exceptions\UnsupportedOrderTypeException;
use AndrewSvirin\Ebics\Exceptions\UnsupportedRequestForOrderInstanceException;
use AndrewSvirin\Ebics\Exceptions\UserUnknownException;
use AndrewSvirin\Ebics\Exceptions\X509CertificateExpiredException;
use AndrewSvirin\Ebics\Exceptions\X509CertificateNotValidYetException;
use AndrewSvirin\Ebics\Exceptions\X509CtlInvalidException;
use AndrewSvirin\Ebics\Exceptions\X509InvalidBasicConstraintsException;
use AndrewSvirin\Ebics\Exceptions\X509InvalidPolicyException;
use AndrewSvirin\Ebics\Exceptions\X509InvalidThumbprintException;
use AndrewSvirin\Ebics\Exceptions\X509UnknownCertificateAuthorityException;
use AndrewSvirin\Ebics\Exceptions\X509WrongAlgorithmException;
use AndrewSvirin\Ebics\Exceptions\X509WrongKeyUsageException;
use AndrewSvirin\Ebics\Factories\EbicsExceptionFactory;
use AndrewSvirin\Ebics\Models\Request;
use AndrewSvirin\Ebics\Models\Response;
use PHPUnit\Framework\TestCase;

class EbicsExceptionFactoryTest extends TestCase
{
    /**
     * @dataProvider getExceptions
     */
    public function testExceptions(string $errorCode, ?string $errorText, string $expectedExceptionClass, ?string $meaning): void
    {
        $exception = EbicsExceptionFactory::buildExceptionFromCode($errorCode, $errorText);
        $this->assertInstanceOf($expectedExceptionClass, $exception);
        $this->assertEquals($errorCode, $exception->getResponseCode());
        $this->assertEquals($meaning, $exception->getMeaning());
        $this->assertNull($exception->getRequest());
        $this->assertNull($exception->getResponse());

        $request  = self::createMock(Request::class);
        $response = self::createMock(Response::class);

        $exception->setRequest($request);
        $exception->setResponse($response);

        $this->assertSame($request, $exception->getRequest());
        $this->assertSame($response, $exception->getResponse());
    }

    /**
     * @dataProvider getExceptions
     */
    public function testExceptionsWithRequestAndReponse(string $errorCode, ?string $errorText, string $expectedExceptionClass, ?string $meaning): void
    {
        $request  = self::createMock(Request::class);
        $response = self::createMock(Response::class);

        $exception = EbicsExceptionFactory::buildExceptionFromCode($errorCode, $errorText, $request, $response);
        $this->assertInstanceOf($expectedExceptionClass, $exception);
        $this->assertEquals($errorCode, $exception->getResponseCode());
        $this->assertEquals($meaning, $exception->getMeaning());
        $this->assertSame($request, $exception->getRequest());
        $this->assertSame($response, $exception->getResponse());
        $this->assertEquals($meaning, $exception->getMessage());
        $this->assertEquals((string) $meaning, $exception->getMessage());
        $this->assertEquals($errorCode, $exception->getCode());
        $this->assertEquals((int) $errorCode, $exception->getCode());
    }

    public function getExceptions(): array
    {
        return [
            ['0', null, EbicsResponseException::class, null],
            ['091302', null, AccountAuthorisationFailedException::class, 'Preliminary verification of the account authorization has failed.'],
            ['091303', null, AmountCheckFailedException::class, 'Preliminary verification of the account amount limit has failed.'],
            ['061001', null, AuthenticationFailedException::class, 'The bank is unable to verify the identification and authentication signature of an EBICS request.'],
            ['090003', null, AuthorisationOrderTypeFailedException::class, 'The subscriber is not entitled to submit orders of the selected order type. If the authorization is missing when the bank verifies whether the subscriber has a bank-technical authorization of signature for the order, the transaction is cancelled.'],
            ['091008', null, BankPubkeyUpdateRequiredException::class, 'The bank verifies the hash value sent by the user. If the hash value does not match the current public keys, the bank terminates the transaction initialization.'],
            ['091219', null, CertificatesValidationErrorException::class, 'The server is unable to match the certificate with the previously declared information automatically.'],
            ['091007', null, DistributedSignatureAuthorisationFailedException::class, 'Subscriber possesses no authorization of signature for the referenced order in the VEU administration.'],
            ['011000', null, DownloadPostprocessDoneException::class, 'The positive acknowledgment of the EBICS response that is sent to the client from the server.'],
            ['011001', null, DownloadPostprocessSkippedException::class, 'The negative acknowledgment of the EBICS response that is sent to the client from the server.'],
            ['091001', null, DownloadSignedOnlyException::class, 'The bank system only supports bank-technically signed download order data for the order request. If the subscriber sets the order attributes to DZHNN and requests the download data without the electronic signature of the bank, the transaction initialization is terminated.'],
            ['091306', null, DuplicateSignatureException::class, 'The signatory has already signed the order.'],
            ['091121', null, IncompatibleOrderAttributeException::class, 'The specified order attribute is not compatible with the order in the bank system. If the bank has a file with the attribute DZHNN or other electronic signature files (for example, with the attribute UZHNN) for the same order, then the use of the order attributes DZHNN is not allowed. Also, if the bank already has the same order and the order was transmitted with the order attributes DZHNN, then again the use of the order attributes DZHNN is not allowed.'],
            ['061099', null, InternalErrorException::class, 'An internal error occurred when processing an EBICS request.'],
            ['091011', null, InvalidHostIdException::class, 'The transmitted host ID is not known to the bank.'],
            ['090004', null, InvalidOrderDataFormatException::class, 'The order data does not correspond with the designated format.'],
            ['091112', null, InvalidOrderParamsException::class, 'In an HVT request, the subscriber specifies the order for which they want to retrieve the VEU transaction details. The HVT request also specifies an offset position in the original order file that marks the starting point of the transaction details to be transmitted. The order details after the specified offset position are returned. If the value specified for offset is higher than the total number of order details, the error EBICS_INVALID_ORDER_PARAMS is returned.'],
            ['091005', null, InvalidOrderTypeException::class, 'Upon verification, the bank finds that the order type specified in invalid.'],
            ['091113', null, InvalidRequestContentException::class, 'The EBICS request does not conform to the XML schema definition specified for individual requests.'],
            ['061002', null, InvalidRequestException::class, 'The received EBICS XML message does not conform to the EBICS specifications.'],
            ['091111', null, InvalidSignatureFileFormatException::class, 'The submitted electronic signature file does not conform to the defined format.'],
            ['091305', null, InvalidSignerStateException::class, 'The state of the signatory is not admissible.'],
            ['091002', null, InvalidUserOrUserStateException::class, 'Error that results from an invalid combination of user ID or an invalid subscriber state.'],
            ['091004', null, InvalidUserStateException::class, 'The identification and authentication signature of the technical user is successfully verified and the non-technical subscriber is known to the bank, but the user is not in a ’Ready’ state.'],
            ['091010', null, InvalidXmlException::class, 'The XML schema does not conform to the EBICS specifications.'],
            ['091218', null, KeymgmtDuplicateKeyException::class, 'The key sent for authentication or encryption is the same as the signature key.'],
            ['091205', null, KeymgmtKeylengthErrorAuthenticationException::class, 'When processing an HIA request, the order data contains an identification and authentication key of inadmissible length.'],
            ['091206', null, KeymgmtKeylengthErrorEncryptionException::class, 'When processing an HIA request, the order data contains an encryption key of inadmissible length.'],
            ['091204', null, KeymgmtKeylengthErrorSignatureException::class, 'When processing an INI request, the order data contains an bank-technical key of inadmissible length.'],
            ['091207', null, KeymgmtNoX509SupportException::class, 'A public key of type X509 is sent to the bank but the bank supports only public key value type.'],
            ['091202', null, KeymgmtUnsupportedVersionAuthenticationException::class, 'When processing an HIA request, the order data contains an inadmissible version of the identification and authentication signature process.'],
            ['091203', null, KeymgmtUnsupportedVersionEncryptionException::class, 'When processing an HIA request, the order data contains an inadmissible version of the encryption process.'],
            ['091201', null, KeymgmtUnsupportedVersionSignatureException::class, 'When processing an INI request, the order data contains an inadmissible version of the bank-technical signature process.'],
            ['091117', null, MaxOrderDataSizeExceededException::class, 'The bank does not support the requested order size.'],
            ['091118', null, MaxSegmentsExceededException::class, 'The submitted number of segments for upload is very high.'],
            ['091119', null, MaxTransactionsExceededException::class, 'The maximum number of parallel transactions per customer is exceeded.'],
            ['090005', null, NoDownloadDataAvailableException::class, 'If the requested download data is not available, the EBICS transaction is terminated.'],
            ['011301', null, NoOnlineChecksException::class, 'The bank does not principally support preliminary verification of orders but the EBICS request contains data for preliminary verification of the order.'],
            ['091217', null, OnlyX509SupportException::class, 'The bank supports evaluation of X.509 data only.'],
            ['031001', null, OrderParamsIgnoredException::class, 'The supplied order parameters that are not supported by the bank are ignored.'],
            ['091115', null, OrderidAlreadyExistsException::class, 'The submitted order number already exists.'],
            ['091114', null, OrderidUnknownException::class, 'Upon verification, the bank finds that the order is not located in the VEU processing system.'],
            ['091120', null, PartnerIdMismatchException::class, 'The partner ID of the electronic signature file differs from the partner ID of the submitter.'],
            ['091116', null, ProcessingErrorException::class, 'When processing an EBICS request, other business-related errors occurred.'],
            ['091105', null, RecoveryNotSupportedException::class, 'If the bank does not support transaction recovery, the upload transaction is terminated.'],
            ['091009', null, SegmentSizeExceededException::class, 'If the size of the transmitted order data segment exceeds 1 MB, the transaction is terminated.'],
            ['091301', null, SignatureVerificationFailedException::class, 'Verification of the electronic signature has failed.'],
            ['091304', null, SignerUnknownException::class, 'The signatory of the order is not a valid subscriber.'],
            ['091102', null, TxAbortException::class, 'If the bank supports transaction recovery, the bank verifies whether an upload transaction can be recovered. If the transaction cannot be recovered, the bank terminates the transaction.'],
            ['091103', null, TxMessageReplayException::class, 'To avoid replay, the bank compares the received Nonce with the list of nonce values that were received previously and stored locally. If the nonce received is greater than the tolerance period specified by the bank, the response EBICS_TX_MESSAGE_REPLAY is returned.'],
            ['061101', null, TxRecoverySyncException::class, 'If the bank supports transaction recovery, the bank verifies whether an upload transaction can be recovered. The server synchronizes with the client to recover the transaction.'],
            ['091104', null, TxSegmentNumberExceededException::class, 'The serial number of the transmitted order data segment must be less than or equal to the total number of data segments that are to be transmitted. The transaction is terminated if the number of transmitted order data segments exceeds the total number of data segments.'],
            ['011101', null, TxSegmentNumberUnderrunException::class, 'The server terminates the transaction if the client, in an upload transaction, has specified a very high (when compared to the number specified in the initialization phase) number of segments that are to be transmitted to the server.'],
            ['091101', null, TxUnknownTxidException::class, 'The supplied transaction ID is invalid.'],
            ['091006', null, UnsupportedOrderTypeException::class, 'Upon verification, the bank finds that the order type specified in valid but not supported by the bank.'],
            ['090006', null, UnsupportedRequestForOrderInstanceException::class, 'In the case of some business transactions, it is not possible to retrieve detailed information of the order data.'],
            ['091003', null, UserUnknownException::class, 'The identification and authentication signature of the technical user is successfully verified but the non-technical subscriber is not known to the bank.'],
            ['091208', null, X509CertificateExpiredException::class, 'The certificate is not valid because it has expired.'],
            ['091209', null, X509CertificateNotValidYetException::class, 'The certificate is not valid because it is not yet in effect.'],
            ['091213', null, X509CtlInvalidException::class, 'When verifying the certificate, the bank detects that the certificate trust list (CTL) is not valid.'],
            ['091216', null, X509InvalidBasicConstraintsException::class, 'The basic constraints are not valid when determining certificate verification.'],
            ['091215', null, X509InvalidPolicyException::class, 'The certificate has invalid policy when determining certificate verification.'],
            ['091212', null, X509InvalidThumbprintException::class, 'The thumb print does not correspond to the certificate.'],
            ['091214', null, X509UnknownCertificateAuthorityException::class, 'The chain cannot be verified because of an unknown certificate authority (CA).'],
            ['091211', null, X509WrongAlgorithmException::class, 'When verifying the certificate algorithm, the bank detects that the certificate is not issued for current use.'],
            ['091210', null, X509WrongKeyUsageException::class, 'When verifying the certificate key usage, the bank detects that the certificate is not issued for current use.'],
        ];
    }
}

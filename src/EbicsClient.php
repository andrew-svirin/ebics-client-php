<?php

namespace AndrewSvirin\Ebics;

use AndrewSvirin\Ebics\Contracts\EbicsClientInterface;
use AndrewSvirin\Ebics\Contracts\HttpClientInterface;
use AndrewSvirin\Ebics\Contracts\OrderDataInterface;
use AndrewSvirin\Ebics\Contracts\SignatureInterface;
use AndrewSvirin\Ebics\Contracts\X509GeneratorInterface;
use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Factories\EbicsExceptionFactory;
use AndrewSvirin\Ebics\Factories\RequestFactory;
use AndrewSvirin\Ebics\Factories\SignatureFactory;
use AndrewSvirin\Ebics\Handlers\OrderDataHandler;
use AndrewSvirin\Ebics\Handlers\ResponseHandler;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\Http\Request;
use AndrewSvirin\Ebics\Models\Http\Response;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Models\Signature;
use AndrewSvirin\Ebics\Models\User;
use AndrewSvirin\Ebics\Services\CryptService;
use AndrewSvirin\Ebics\Services\HttpClient;
use DateTime;
use DateTimeInterface;
use LogicException;

/**
 * EBICS client representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class EbicsClient implements EbicsClientInterface
{
    public const VERSION_25 = "VERSION_25";
    public const VERSION_30 = "VERSION_30";

    /**
     * An EbicsBank instance.
     *
     * @var Bank
     */
    private $bank;

    /**
     * EBICS protocol version
     *
     * @var string
     */
    private $version;

    /**
     * An EbicsUser instance.
     *
     * @var User
     */
    private $user;

    /**
     * @var KeyRing
     */
    private $keyRing;

    /**
     * @var OrderDataHandler
     */
    private $orderDataHandler;

    /**
     * @var ResponseHandler
     */
    private $responseHandler;

    /**
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * @var CryptService
     */
    private $cryptService;

    /**
     * @var SignatureFactory
     */
    private $signatureFactory;

    /**
     * @var X509GeneratorInterface|null
     */
    private $x509Generator;

    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * Constructor.
     *
     * @param Bank $bank
     * @param User $user
     * @param KeyRing $keyRing
     */
    public function __construct(Bank $bank, User $user, KeyRing $keyRing, string $version = self::VERSION_25)
    {
        $this->bank = $bank;
        $this->version = $version;
        $this->user = $user;
        $this->keyRing = $keyRing;
        $this->requestFactory = new RequestFactory($bank, $user, $keyRing, $version);
        $this->orderDataHandler = new OrderDataHandler($bank, $user, $keyRing, $version);
        $this->responseHandler = new ResponseHandler();
        $this->cryptService = new CryptService();
        $this->signatureFactory = new SignatureFactory();
        // Set default http client.
        $this->httpClient = new HttpClient();
    }

    /**
     * @inheritDoc
     */
    public function createUserSignatures(): void
    {
        $signatureA = $this->getUserSignature(Signature::TYPE_A, true);
        $this->keyRing->setUserSignatureA($signatureA);

        $signatureE = $this->getUserSignature(Signature::TYPE_E, true);
        $this->keyRing->setUserSignatureE($signatureE);

        $signatureX = $this->getUserSignature(Signature::TYPE_X, true);
        $this->keyRing->setUserSignatureX($signatureX);
    }

    /**
     * @inheritDoc
     */
    public function HEV(): Response
    {
        $request = $this->requestFactory->createHEV();
        $response = $this->httpClient->post($this->bank->getUrl(), $request);

        $this->checkH000ReturnCode($request, $response);

        return $response;
    }

    /**
     * @inheritDoc
     *
     * @throws EbicsException
     */
    public function INI(DateTimeInterface $dateTime = null, bool $createSignature = false): Response
    {
        if (null === $dateTime) {
            $dateTime = new DateTime();
        }

        $signatureA = $this->getUserSignature(Signature::TYPE_A, $createSignature);

        $request = $this->requestFactory->createINI($signatureA, $dateTime);
        $response = $this->httpClient->post($this->bank->getUrl(), $request);

        switch ($this->version) {
            case self::VERSION_30:
                $this->checkH005ReturnCode($request, $response);
                break;
                
            case self::VERSION_25:
                $this->checkH004ReturnCode($request, $response);
                break;
        }
        $this->keyRing->setUserSignatureA($signatureA);

        return $response;
    }

    /**
     * @inheritDoc
     * @throws EbicsException
     */
    public function HIA(DateTimeInterface $dateTime = null, bool $createSignature = false): Response
    {
        if (null === $dateTime) {
            $dateTime = new DateTime();
        }

        $signatureE = $this->getUserSignature(Signature::TYPE_E, $createSignature);
        $signatureX = $this->getUserSignature(Signature::TYPE_X, $createSignature);

        $request = $this->requestFactory->createHIA($signatureE, $signatureX, $dateTime);
        $response = $this->httpClient->post($this->bank->getUrl(), $request);

        switch ($this->version) {
            case self::VERSION_30:
                $this->checkH005ReturnCode($request, $response);
                break;
                
            case self::VERSION_25:
                $this->checkH004ReturnCode($request, $response);
                break;
        }
        $this->keyRing->setUserSignatureE($signatureE);
        $this->keyRing->setUserSignatureX($signatureX);

        return $response;
    }

    /**
     * @inheritDoc
     * @throws Exceptions\EbicsException
     */
    public function BTD(
        string $serviceName, 
        string $scope, 
        string $msgName, 
        DateTimeInterface $dateTime = null, 
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null
    ): string {
        if (null === $dateTime) {
            $dateTime = new DateTime();
        }
        $request = $this->requestFactory->createBTD($dateTime, $serviceName, $scope, $msgName, $startDateTime, $endDateTime);
        $response = $this->httpClient->post($this->bank->getUrl(), $request);

        $this->checkH005ReturnCode($request, $response);
        
        $transaction = $this->responseHandler->retrieveTransaction($response, $this->version);
        $response->addTransaction($transaction);
        $orderData = $this->responseHandler->retrievePlainOrderData($response, $transaction->getKey(), $this->keyRing, $this->version);

        return $orderData;
    }

    /**
     * @inheritDoc
     * @throws Exceptions\EbicsException
     */
    public function HPB(DateTimeInterface $dateTime = null): Response
    {
        if (null === $dateTime) {
            $dateTime = new DateTime();
        }
        $request = $this->requestFactory->createHPB($dateTime);
        $response = $this->httpClient->post($this->bank->getUrl(), $request);

        switch ($this->version) {
            case self::VERSION_30:
                $this->checkH005ReturnCode($request, $response);
                break;
                
            case self::VERSION_25:
                $this->checkH004ReturnCode($request, $response);
                break;
        }
        $transaction = $this->responseHandler->retrieveTransaction($response, $this->version);
        $response->addTransaction($transaction);
        $orderData = $this->responseHandler->retrieveOrderData($response, $transaction->getKey(), $this->keyRing, $this->version);
        $signatureX = $this->orderDataHandler->retrieveAuthenticationSignature($orderData);
        $signatureE = $this->orderDataHandler->retrieveEncryptionSignature($orderData);
        $this->keyRing->setBankSignatureX($signatureX);
        $this->keyRing->setBankSignatureE($signatureE);

        return $response;
    }

    /**
     * @inheritDoc
     * @throws Exceptions\EbicsException
     */
    public function HPD(DateTimeInterface $dateTime = null): Response
    {
        if (null === $dateTime) {
            $dateTime = new DateTime();
        }
        $request = $this->requestFactory->createHPD($dateTime);
        $response = $this->retrieveOrderData($request);

        return $response;
    }

    /**
     * @inheritDoc
     * @throws Exceptions\EbicsException
     */
    public function HKD(DateTimeInterface $dateTime = null): Response
    {
        if (null === $dateTime) {
            $dateTime = new DateTime();
        }
        $request = $this->requestFactory->createHKD($dateTime);
        $response = $this->retrieveOrderData($request);

        return $response;
    }

    /**
     * @inheritDoc
     * @throws Exceptions\EbicsException
     */
    public function HTD(DateTimeInterface $dateTime = null): Response
    {
        if (null === $dateTime) {
            $dateTime = new DateTime();
        }

        $request = $this->requestFactory->createHTD($dateTime);
        $response = $this->retrieveOrderData($request);

        return $response;
    }

    /**
     * @inheritDoc
     * @throws Exceptions\EbicsException
     */
    public function PTK(DateTimeInterface $dateTime = null): Response
    {
        if (null === $dateTime) {
            $dateTime = new DateTime();
        }
        $request = $this->requestFactory->createPTK($dateTime);
        $response = $this->retrievePlainOrderData($request);

        return $response;
    }

    /**
     * @inheritDoc
     * @throws Exceptions\EbicsException
     */
    public function HAA(DateTimeInterface $dateTime = null): Response
    {
        if (null === $dateTime) {
            $dateTime = new DateTime();
        }
        $request = $this->requestFactory->createHAA($dateTime);
        $response = $this->retrieveOrderData($request);

        return $response;
    }

    /**
     * @inheritDoc
     * @throws Exceptions\EbicsException
     */
    public function VMK(
        DateTimeInterface $dateTime = null,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null
    ): Response {
        if (null === $dateTime) {
            $dateTime = new DateTime();
        }
        $request = $this->requestFactory->createVMK($dateTime, $startDateTime, $endDateTime);
        $response = $this->retrievePlainOrderData($request);

        return $response;
    }

    /**
     * @inheritDoc
     * @throws Exceptions\EbicsException
     */
    public function STA(
        DateTimeInterface $dateTime = null,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null
    ): Response {
        if (null === $dateTime) {
            $dateTime = new DateTime();
        }
        $request = $this->requestFactory->createSTA($dateTime, $startDateTime, $endDateTime);
        $response = $this->retrievePlainOrderData($request);

        return $response;
    }

    /**
     * @inheritDoc
     * @throws Exceptions\EbicsException
     */
    // @codingStandardsIgnoreStart
    public function C53(
        DateTimeInterface $dateTime = null,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null
    ): Response {
        // @codingStandardsIgnoreEnd
        if (null === $dateTime) {
            $dateTime = new DateTime();
        }
        $request = $this->requestFactory->createC53($dateTime, $startDateTime, $endDateTime);
        $response = $this->retrieveOrderDataItems($request);

        return $response;
    }

    /**
     * @inheritDoc
     * @throws Exceptions\EbicsException
     */
    // @codingStandardsIgnoreStart
    public function Z53(
        DateTimeInterface $dateTime = null,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null
    ): Response {
        // @codingStandardsIgnoreEnd
        if (null === $dateTime) {
            $dateTime = new DateTime();
        }
        $request = $this->requestFactory->createZ53($dateTime, $startDateTime, $endDateTime);
        $response = $this->retrievePlainOrderData($request);

        return $response;
    }

    /**
     * @inheritDoc
     * @throws Exceptions\EbicsException
     */
    // @codingStandardsIgnoreStart
    public function Z54(
        DateTimeInterface $dateTime = null,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null
    ): Response {
        // @codingStandardsIgnoreEnd
        if (null === $dateTime) {
            $dateTime = new DateTime();
        }
        $request = $this->requestFactory->createZ54($dateTime, $startDateTime, $endDateTime);
        $response = $this->retrievePlainOrderData($request);

        return $response;
    }

    /**
     * @inheritDoc
     * @throws Exceptions\EbicsException
     */
    public function FDL(
        $fileInfo,
        $format = 'plain',
        $countryCode = 'FR',
        DateTimeInterface $dateTime = null,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null
    ): Response {
        if (null === $dateTime) {
            $dateTime = new DateTime();
        }

        $request = $this->requestFactory->createFDL($dateTime, $fileInfo, $countryCode, $startDateTime, $endDateTime);
        switch ($format) {
            case 'plain':
                $response = $this->retrievePlainOrderData($request);
                break;
            case 'xml':
            default:
                $response = $this->retrieveOrderData($request);
                break;
        }

        return $response;
    }

    /**
     * @inheritDoc
     * @throws Exceptions\EbicsResponseException
     */
    public function CCT(
        OrderDataInterface $orderData,
        DateTimeInterface $dateTime = null,
        int $numSegments = 1
    ): Response {
        if (null === $dateTime) {
            $dateTime = new DateTime();
        }

        $transactionKey = $this->cryptService->generateTransactionKey();

        $request = $this->requestFactory->createCCT($dateTime, $numSegments, $transactionKey, $orderData);
        $response = $this->retrieveTransaction($request, $orderData, $numSegments, $transactionKey);

        return $response;
    }

    /**
     * @inheritDoc
     * @throws Exceptions\EbicsResponseException
     */
    public function CDD(
        OrderDataInterface $orderData,
        DateTimeInterface $dateTime = null,
        int $numSegments = 1
    ): Response {
        if (null === $dateTime) {
            $dateTime = new DateTime();
        }

        $transactionKey = $this->cryptService->generateTransactionKey();

        $request = $this->requestFactory->createCDD($dateTime, $numSegments, $transactionKey, $orderData);
        $response = $this->retrieveTransaction($request, $orderData, $numSegments, $transactionKey);

        return $response;
    }

    /**
     * @inheritDoc
     * @throws EbicsException
     */
    public function transferReceipt(Response $response, bool $acknowledged = true): Response
    {
        $lastTransaction = $response->getLastTransaction();
        if (null === $lastTransaction) {
            throw new EbicsException('There is no transactions to mark as received.');
        }

        $request = $this->requestFactory->createTransferReceipt($lastTransaction->getId(), $acknowledged);
        $response = $this->httpClient->post($this->bank->getUrl(), $request);

        $this->checkH004ReturnCode($request, $response);

        return $response;
    }

    /**
     * @inheritDoc
     * @throws EbicsException
     */
    public function transferTransfer(Response $response): Response
    {
        $lastTransaction = $response->getLastTransaction();
        if (null === $lastTransaction) {
            throw new EbicsException('There is no transactions to mark as transferred.');
        }

        $lastTransaction->setSegmentNumber(1);
        $request = $this->requestFactory->createTransferTransfer(
            $lastTransaction->getId(),
            $lastTransaction->getKey(),
            $lastTransaction->getOrderData(),
            $lastTransaction->getSegmentNumber()
        );
        $response = $this->httpClient->post($this->bank->getUrl(), $request);

        $this->checkH004ReturnCode($request, $response);

        return $response;
    }

    /**
     * @param Request $request
     * @param Response $response
     *
     * @throws Exceptions\EbicsResponseException
     */
    private function checkH004ReturnCode(Request $request, Response $response): void
    {
        $errorCode = $this->responseHandler->retrieveH004BodyOrHeaderReturnCode($response);

        if ('000000' === $errorCode) {
            return;
        }

        // For Transaction Done.
        if ('011000' === $errorCode) {
            return;
        }

        $reportText = $this->responseHandler->retrieveH004ReportText($response);
        EbicsExceptionFactory::buildExceptionFromCode($errorCode, $reportText, $request, $response);
    }

    /**
     * @param Request $request
     * @param Response $response
     *
     * @throws Exceptions\EbicsResponseException
     */
    private function checkH005ReturnCode(Request $request, Response $response): void
    {
        $errorCode = $this->responseHandler->retrieveH005BodyOrHeaderReturnCode($response);

        if ('000000' === $errorCode) {
            return;
        }

        // For Transaction Done.
        if ('011000' === $errorCode) {
            return;
        }

        $reportText = $this->responseHandler->retrieveH005ReportText($response);
        EbicsExceptionFactory::buildExceptionFromCode($errorCode, $reportText, $request, $response);
    }

    /**
     * @param Request $request
     * @param Response $response
     *
     * @throws Exceptions\EbicsResponseException
     */
    private function checkH000ReturnCode(Request $request, Response $response): void
    {
        $errorCode = $this->responseHandler->retrieveH000ReturnCode($response);

        if ('000000' === $errorCode) {
            return;
        }

        $reportText = $this->responseHandler->retrieveH000ReportText($response);
        EbicsExceptionFactory::buildExceptionFromCode($errorCode, $reportText, $request, $response);
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws EbicsException
     * @throws Exceptions\EbicsResponseException
     */
    private function retrieveOrderData(Request $request)
    {
        $response = $this->httpClient->post($this->bank->getUrl(), $request);

        $this->checkH004ReturnCode($request, $response);

        $transaction = $this->responseHandler->retrieveTransaction($response, $this->version);
        $response->addTransaction($transaction);

        $orderData = $this->responseHandler->retrieveOrderData($response, $transaction->getKey(), $this->keyRing, $this->version);
        $transaction->setOrderData($orderData);

        return $response;
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws EbicsException
     * @throws Exceptions\EbicsResponseException
     */
    private function retrievePlainOrderData(Request $request)
    {
        $response = $this->httpClient->post($this->bank->getUrl(), $request);

        $this->checkH004ReturnCode($request, $response);

        $transaction = $this->responseHandler->retrieveTransaction($response, $this->version);
        $response->addTransaction($transaction);

        $plainOrderData = $this->responseHandler->retrievePlainOrderData(
            $response,
            $transaction->getKey(),
            $this->keyRing,
            $this->version
        );
        $transaction->setPlainOrderData($plainOrderData);

        return $response;
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws EbicsException
     * @throws Exceptions\EbicsResponseException
     */
    private function retrieveOrderDataItems(Request $request)
    {
        $response = $this->httpClient->post($this->bank->getUrl(), $request);

        $this->checkH004ReturnCode($request, $response);

        $transaction = $this->responseHandler->retrieveTransaction($response, $this->version);
        $response->addTransaction($transaction);

        $plainOrderData = $this->responseHandler->retrieveOrderDataItems(
            $response,
            $transaction->getKey(),
            $this->keyRing,
            $this->version
        );
        $transaction->setOrderDataItems($plainOrderData);

        return $response;
    }

    /**
     * @param Request $request
     * @param OrderDataInterface $orderData
     * @param int $numSegments
     * @param string $transactionKey
     *
     * @return Response
     * @throws Exceptions\EbicsResponseException
     */
    private function retrieveTransaction(
        Request $request,
        OrderDataInterface $orderData,
        int $numSegments,
        string $transactionKey
    ) {
        $response = $this->httpClient->post($this->bank->getUrl(), $request);

        $this->checkH004ReturnCode($request, $response);

        $transaction = $this->responseHandler->retrieveTransaction($response, $this->version);
        $transaction->setOrderData($orderData);
        $transaction->setNumSegments($numSegments);
        $transaction->setKey($transactionKey);
        $response->addTransaction($transaction);

        return $response;
    }

    /**
     * @return KeyRing
     */
    public function getKeyRing(): KeyRing
    {
        return $this->keyRing;
    }

    /**
     * @return Bank
     */
    public function getBank(): Bank
    {
        return $this->bank;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @inheritDoc
     */
    public function setX509Generator(X509GeneratorInterface $x509Generator = null): void
    {
        $this->x509Generator = $x509Generator;
    }

    /**
     * @inheritDoc
     */
    public function setHttpClient(HttpClientInterface $httpClient): void
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Get user signature.
     * @param string $type One of allowed user signature type.
     * @param bool $createNew Flag to generate new signature force.
     *
     * @return SignatureInterface
     * @throws EbicsException
     */
    private function getUserSignature(string $type, bool $createNew = false): SignatureInterface
    {
        switch ($type) {
            case Signature::TYPE_A:
                $signature = $this->keyRing->getUserSignatureA();
                break;
            case Signature::TYPE_E:
                $signature = $this->keyRing->getUserSignatureE();
                break;
            case Signature::TYPE_X:
                $signature = $this->keyRing->getUserSignatureX();
                break;
            default:
                throw new LogicException(sprintf('Type "%s" not allowed', $type));
        }

        if (!$signature || $createNew) {
            $newSignature = $this->createUserSignature($type);
        }

        return $newSignature ?? $signature;
    }

    /**
     * Create new signature.
     * @param string $type
     * @return SignatureInterface
     * @throws EbicsException
     */
    private function createUserSignature(string $type): SignatureInterface
    {
        switch ($type) {
            case Signature::TYPE_A:
                $signature = $this->signatureFactory->createSignatureAFromKeys(
                    $this->cryptService->generateKeys($this->keyRing->getPassword()),
                    $this->keyRing->getPassword(),
                    $this->bank->isCertified() ? $this->x509Generator : null
                );
                break;
            case Signature::TYPE_E:
                $signature = $this->signatureFactory->createSignatureEFromKeys(
                    $this->cryptService->generateKeys($this->keyRing->getPassword()),
                    $this->keyRing->getPassword(),
                    $this->bank->isCertified() ? $this->x509Generator : null
                );
                break;
            case Signature::TYPE_X:
                $signature = $this->signatureFactory->createSignatureXFromKeys(
                    $this->cryptService->generateKeys($this->keyRing->getPassword()),
                    $this->keyRing->getPassword(),
                    $this->bank->isCertified() ? $this->x509Generator : null
                );
                break;
            default:
                throw new LogicException(sprintf('Type "%s" not allowed', $type));
        }

        return $signature;
    }
}

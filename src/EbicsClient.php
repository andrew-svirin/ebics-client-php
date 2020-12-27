<?php

namespace AndrewSvirin\Ebics;

use AndrewSvirin\Ebics\Contracts\EbicsClientInterface;
use AndrewSvirin\Ebics\Contracts\X509GeneratorInterface;
use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Factories\CertificateFactory;
use AndrewSvirin\Ebics\Factories\EbicsExceptionFactory;
use AndrewSvirin\Ebics\Factories\TransactionFactory;
use AndrewSvirin\Ebics\Handlers\OrderDataHandler;
use AndrewSvirin\Ebics\Handlers\RequestHandler;
use AndrewSvirin\Ebics\Handlers\ResponseHandler;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Models\Request;
use AndrewSvirin\Ebics\Models\Response;
use AndrewSvirin\Ebics\Models\User;
use AndrewSvirin\Ebics\Services\CryptService;
use DateTime;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * EBICS client representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class EbicsClient implements EbicsClientInterface
{
    /**
     * An EbicsBank instance.
     *
     * @var Bank
     */
    private $bank;

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
     * @var RequestHandler
     */
    private $requestFactory;

    /**
     * @var CryptService
     */
    private $cryptService;

    /**
     * @var CertificateFactory
     */
    private $certificateFactory;

    /**
     * @var X509GeneratorInterface|null
     */
    private $x509Generator;

    /**
     * Constructor.
     *
     * @param Bank $bank
     * @param User $user
     * @param KeyRing $keyRing
     */
    public function __construct(Bank $bank, User $user, KeyRing $keyRing)
    {
        $this->bank = $bank;
        $this->user = $user;
        $this->keyRing = $keyRing;
        $this->requestFactory = new RequestHandler($bank, $user, $keyRing);
        $this->orderDataHandler = new OrderDataHandler($bank, $user, $keyRing);
        $this->responseHandler = new ResponseHandler();
        $this->cryptService = new CryptService();
        $this->certificateFactory = new CertificateFactory();
    }

    /**
     * Make request to bank server.
     *
     * @param Request $request
     *
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function post(Request $request)
    {
        $body = $request->getContent();
        $httpClient = HttpClient::create();

        return $httpClient->request('POST', $this->bank->getUrl(), [
            'headers' => [
                'Content-Type' => 'text/xml; charset=ISO-8859-1',
            ],
            'body' => $body,
            'verify_peer' => false,
            'verify_host' => false,
        ]);
    }

    /**
     * @inheritDoc
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function HEV(): Response
    {
        $request = $this->requestFactory->buildHEV();
        $hostResponse = $this->post($request);
        $hostResponseContent = $hostResponse->getContent();
        $response = new Response();
        $response->loadXML($hostResponseContent);
        $this->checkH000ReturnCode($request, $response);

        return $response;
    }

    /**
     * @inheritDoc
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface|EbicsException
     */
    public function INI(DateTime $dateTime = null): Response
    {
        if (null === $dateTime) {
            $dateTime = new DateTime();
        }
        $certificateA = $this->certificateFactory->generateCertificateAFromKeys(
            $this->cryptService->generateKeys($this->keyRing),
            $this->bank->isCertified() ? $this->x509Generator : null
        );
        $request = $this->requestFactory->buildINI($certificateA, $dateTime);
        $hostResponse = $this->post($request);
        $hostResponseContent = $hostResponse->getContent();
        $response = new Response();
        $response->loadXML($hostResponseContent);

        $this->checkH004ReturnCode($request, $response);
        $this->keyRing->setUserCertificateA($certificateA);

        return $response;
    }

    /**
     * @inheritDoc
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws EbicsException
     */
    public function HIA(DateTime $dateTime = null): Response
    {
        if (null === $dateTime) {
            $dateTime = new DateTime();
        }
        $certificateE = $this->certificateFactory->generateCertificateEFromKeys(
            $this->cryptService->generateKeys($this->keyRing),
            $this->bank->isCertified() ? $this->x509Generator : null
        );
        $certificateX = $this->certificateFactory->generateCertificateXFromKeys(
            $this->cryptService->generateKeys($this->keyRing),
            $this->bank->isCertified() ? $this->x509Generator : null
        );
        $request = $this->requestFactory->buildHIA($certificateE, $certificateX, $dateTime);
        $hostResponse = $this->post($request);
        $hostResponseContent = $hostResponse->getContent();
        $response = new Response();
        $response->loadXML($hostResponseContent);

        $this->checkH004ReturnCode($request, $response);
        $this->keyRing->setUserCertificateE($certificateE);
        $this->keyRing->setUserCertificateX($certificateX);

        return $response;
    }

    /**
     * @inheritDoc
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exceptions\EbicsException
     */
    public function HPB(DateTime $dateTime = null): Response
    {
        if (null === $dateTime) {
            $dateTime = new DateTime();
        }
        $request = $this->requestFactory->buildHPB($dateTime);
        $hostResponse = $this->post($request);
        $hostResponseContent = $hostResponse->getContent();
        $response = new Response();
        $response->loadXML($hostResponseContent);

        $this->checkH004ReturnCode($request, $response);
        // Prepare decrypted OrderData.
        $orderDataEncrypted = $this->responseHandler->retrieveOrderData($response);
        $orderData = $this->cryptService->decryptOrderData($this->keyRing, $orderDataEncrypted);
        $response->addTransaction(TransactionFactory::buildTransactionFromOrderData($orderData));
        $certificateX = $this->orderDataHandler->retrieveAuthenticationCertificate($orderData);
        $certificateE = $this->orderDataHandler->retrieveEncryptionCertificate($orderData);
        $this->keyRing->setBankCertificateX($certificateX);
        $this->keyRing->setBankCertificateE($certificateE);

        return $response;
    }

    /**
     * @inheritDoc
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exceptions\EbicsException
     */
    public function HPD(DateTime $dateTime = null): Response
    {
        if (null === $dateTime) {
            $dateTime = new DateTime();
        }
        $request = $this->requestFactory->buildHPD($dateTime);
        $hostResponse = $this->post($request);
        $hostResponseContent = $hostResponse->getContent();
        $response = new Response();
        $response->loadXML($hostResponseContent);

        $this->checkH004ReturnCode($request, $response);
        $transaction = $this->responseHandler->retrieveTransaction($response);
        $response->addTransaction($transaction);
        // Prepare decrypted OrderData.
        $orderDataEncrypted = $this->responseHandler->retrieveOrderData($response);
        $orderData = $this->cryptService->decryptOrderData($this->keyRing, $orderDataEncrypted);
        $transaction->setOrderData($orderData);

        return $response;
    }

    /**
     * @inheritDoc
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exceptions\EbicsException
     */
    public function HKD(DateTime $dateTime = null): Response
    {
        if (null === $dateTime) {
            $dateTime = new DateTime();
        }
        $request = $this->requestFactory->buildHKD($dateTime);
        $hostResponse = $this->post($request);
        $hostResponseContent = $hostResponse->getContent();
        $response = new Response();
        $response->loadXML($hostResponseContent);

        $this->checkH004ReturnCode($request, $response);
        $transaction = $this->responseHandler->retrieveTransaction($response);
        $response->addTransaction($transaction);
        // Prepare decrypted OrderData.
        $orderDataEncrypted = $this->responseHandler->retrieveOrderData($response);
        $orderData = $this->cryptService->decryptOrderData($this->keyRing, $orderDataEncrypted);
        $transaction->setOrderData($orderData);

        return $response;
    }

    /**
     * @inheritDoc
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exceptions\EbicsException
     */
    public function HTD(DateTime $dateTime = null): Response
    {
        if (null === $dateTime) {
            $dateTime = new DateTime();
        }

        $request = $this->requestFactory->buildHTD($dateTime);
        $hostResponse = $this->post($request);
        $hostResponseContent = $hostResponse->getContent();
        $response = new Response();
        $response->loadXML($hostResponseContent);

        $this->checkH004ReturnCode($request, $response);
        $transaction = $this->responseHandler->retrieveTransaction($response);
        $response->addTransaction($transaction);
        // Prepare decrypted OrderData.
        $orderDataEncrypted = $this->responseHandler->retrieveOrderData($response);
        $orderData = $this->cryptService->decryptOrderData($this->keyRing, $orderDataEncrypted);
        $transaction->setOrderData($orderData);

        return $response;
    }

    /**
     * @inheritDoc
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exceptions\EbicsException
     */
    public function FDL(
        $fileInfo,
        $format = 'plain',
        $countryCode = 'FR',
        DateTime $dateTime = null,
        DateTime $startDateTime = null,
        DateTime $endDateTime = null
    ): Response {
        if (null === $dateTime) {
            $dateTime = new DateTime();
        }

        $request = $this->requestFactory->buildFDL($dateTime, $fileInfo, $countryCode, $startDateTime, $endDateTime);
        $hostResponse = $this->post($request);
        $hostResponseContent = $hostResponse->getContent();
        $response = new Response();
        $response->loadXML($hostResponseContent);

        $this->checkH004ReturnCode($request, $response);

        $transaction = $this->responseHandler->retrieveTransaction($response);
        $response->addTransaction($transaction);

        // Prepare decrypted datas.
        $orderDataEncrypted = $this->responseHandler->retrieveOrderData($response);
        switch ($format) {
            case 'plain':
                $orderDataContent = $this->cryptService->decryptOrderDataContent($this->keyRing, $orderDataEncrypted);
                $transaction->setPlainOrderData($orderDataContent);
                break;
            case 'xml':
            default:
                $orderData = $this->cryptService->decryptOrderData($this->keyRing, $orderDataEncrypted);
                $transaction->setOrderData($orderData);
                break;
        }

        return $response;
    }

    /**
     * @inheritDoc
     */
    public function transferReceipt(Response $response, bool $acknowledged = true): Response
    {
        $lastTransaction = $response->getLastTransaction();
        if (null === $lastTransaction) {
            throw new EbicsException('There is no transactions to mark as received');
        }

        $request = $this->requestFactory->buildTransferReceipt($lastTransaction->getId(), $acknowledged);
        $hostResponse = $this->post($request);
        $hostResponseContent = $hostResponse->getContent();
        $response = new Response();
        $response->loadXML($hostResponseContent);

        $this->checkH004ReturnCode($request, $response);

        return $response;
    }

    /**
     * @inheritDoc
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exceptions\EbicsException
     */
    public function HAA(DateTime $dateTime = null, string $phase = null, string $transactionId = null): Response
    {
        if (null === $dateTime) {
            $dateTime = new DateTime();
        }
        $request = $this->requestFactory->buildHAA($dateTime);
        $hostResponse = $this->post($request);
        $hostResponseContent = $hostResponse->getContent();
        $response = new Response();
        $response->loadXML($hostResponseContent);

        $this->checkH004ReturnCode($request, $response);
        $transaction = $this->responseHandler->retrieveTransaction($response);
        $response->addTransaction($transaction);
        // Prepare decrypted OrderData.
        $orderDataEncrypted = $this->responseHandler->retrieveOrderData($response);
        $orderData = $this->cryptService->decryptOrderData($this->keyRing, $orderDataEncrypted);
        $transaction->setOrderData($orderData);

        return $response;
    }

    /**
     * @inheritDoc
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exceptions\EbicsException
     */
    public function VMK(
        DateTime $dateTime = null,
        DateTime $startDateTime = null,
        DateTime $endDateTime = null
    ): Response {
        if (null === $dateTime) {
            $dateTime = new DateTime();
        }
        $request = $this->requestFactory->buildVMK($dateTime, $startDateTime, $endDateTime);
        $hostResponse = $this->post($request);
        $hostResponseContent = $hostResponse->getContent();
        $response = new Response();
        $response->loadXML($hostResponseContent);

        $this->checkH004ReturnCode($request, $response);

        return $response;
    }

    /**
     * @inheritDoc
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exceptions\EbicsException
     */
    public function STA(
        DateTime $dateTime = null,
        DateTime $startDateTime = null,
        DateTime $endDateTime = null
    ): Response {
        if (null === $dateTime) {
            $dateTime = new DateTime();
        }
        $request = $this->requestFactory->buildSTA($dateTime, $startDateTime, $endDateTime);
        $hostResponse = $this->post($request);
        $hostResponseContent = $hostResponse->getContent();
        $response = new Response();
        $response->loadXML($hostResponseContent);

        $this->checkH004ReturnCode($request, $response);

        return $response;
    }

    /**
     * @inheritDoc
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exceptions\EbicsException
     */
    // @codingStandardsIgnoreStart
    public function C53(
        DateTime $dateTime = null,
        DateTime $startDateTime = null,
        DateTime $endDateTime = null
    ): Response {
        // @codingStandardsIgnoreEnd
        if (null === $dateTime) {
            $dateTime = new DateTime();
        }
        $request = $this->requestFactory->buildC53($dateTime, $startDateTime, $endDateTime);
        $hostResponse = $this->post($request);
        $hostResponseContent = $hostResponse->getContent();
        $response = new Response();
        $response->loadXML($hostResponseContent);

        $this->checkH004ReturnCode($request, $response);

        return $response;
    }

    /**
     * @inheritDoc
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exceptions\EbicsException
     */
    // @codingStandardsIgnoreStart
    public function Z53(
        DateTime $dateTime = null,
        DateTime $startDateTime = null,
        DateTime $endDateTime = null
    ): Response {
        // @codingStandardsIgnoreEnd
        if (null === $dateTime) {
            $dateTime = new DateTime();
        }
        $request = $this->requestFactory->buildZ53($dateTime, $startDateTime, $endDateTime);
        $hostResponse = $this->post($request);
        $hostResponseContent = $hostResponse->getContent();
        $response = new Response();
        $response->loadXML($hostResponseContent);

        $this->checkH004ReturnCode($request, $response);

        return $response;
    }

    /**
     * @param Request $request
     * @param Response $response
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
        throw EbicsExceptionFactory::buildExceptionFromCode($errorCode, $reportText, $request, $response);
    }

    /**
     * @param Request $request
     * @param Response $response
     */
    private function checkH000ReturnCode(Request $request, Response $response): void
    {
        $errorCode = $this->responseHandler->retrieveH000ReturnCode($response);

        if ('000000' === $errorCode) {
            return;
        }

        $reportText = $this->responseHandler->retrieveH000ReportText($response);
        throw EbicsExceptionFactory::buildExceptionFromCode($errorCode, $reportText, $request, $response);
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
     * @inheritDoc
     */
    public function setX509Generator(X509GeneratorInterface $x509Generator = null): void
    {
        $this->x509Generator = $x509Generator;
    }
}

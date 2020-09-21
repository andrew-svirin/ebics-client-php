<?php

namespace AndrewSvirin\Ebics;

use AndrewSvirin\Ebics\Contracts\EbicsClientInterface;
use AndrewSvirin\Ebics\Contracts\EbicsResponseExceptionInterface;
use AndrewSvirin\Ebics\Exceptions\DownloadPostprocessDoneException;
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
use AndrewSvirin\Ebics\Models\Transaction;
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
     * @var RequestMaker
     */
    private $requestMaker;
    /**
     * @var CryptService|null
     */
    private $cryptService;
    /**
     * @var CertificateFactory|null
     */
    private $certificateFactory;

    public function __construct(
        RequestMaker $requestMaker = null,
        RequestHandler $requestHandler = null,
        ResponseHandler $responseHandler = null,
        CryptService $cryptService  = null,
        CertificateFactory $certificateFactory = null,
        OrderDataHandler $orderDataHandler = null
    ) {
        $this->requestMaker = $requestMaker === null ? new RequestMaker(HttpClient::create()) : $requestMaker;
        $this->requestFactory = $requestHandler === null ? new RequestHandler() : $requestHandler;
        $this->responseHandler = $responseHandler === null ? new ResponseHandler() : $responseHandler;
        $this->orderDataHandler = $orderDataHandler === null ? new OrderDataHandler() : $orderDataHandler;
        $this->cryptService = $cryptService === null ? new CryptService() : $cryptService;
        $this->certificateFactory = $certificateFactory === null ? new CertificateFactory() : $certificateFactory;
    }

    /**
     * {@inheritdoc}
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function HEV(Bank $bank): Response
    {
        $request  = $this->requestFactory->buildHEV($bank);
        $response = $this->requestMaker->post($request, $bank);

        return $this->responseHandler->checkH000ReturnCode($request, $response);
    }

    /**
     * {@inheritdoc}
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function INI(Bank $bank, User $user, KeyRing $keyRing, DateTime $dateTime = null): Response
    {
        $certificateA = $this->certificateFactory->generateCertificateAFromKeys($this->cryptService->generateKeys($keyRing), $bank->isCertified());
        $request = $this->requestFactory->buildINI($bank, $user, $keyRing, $certificateA, $dateTime ?? new DateTime());
        $response = $this->requestMaker->post($request, $bank);

        $this->responseHandler->checkH004ReturnCode($request, $response);
        $keyRing->setUserCertificateA($certificateA);

        return $response;
    }

    /**
     * {@inheritdoc}
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function HIA(Bank $bank, User $user, KeyRing $keyRing, DateTime $dateTime = null): Response
    {
        $certificateE = $this->certificateFactory->generateCertificateEFromKeys($this->cryptService->generateKeys($keyRing), $bank->isCertified());
        $certificateX = $this->certificateFactory->generateCertificateXFromKeys($this->cryptService->generateKeys($keyRing), $bank->isCertified());
        $request = $this->requestFactory->buildHIA($bank, $user, $keyRing, $certificateE, $certificateX, $dateTime ?? new DateTime());
        $response = $this->requestMaker->post($request, $bank);

        $this->responseHandler->checkH004ReturnCode($request, $response);
        $keyRing->setUserCertificateE($certificateE);
        $keyRing->setUserCertificateX($certificateX);

        return $response;
    }

    /**
     * {@inheritdoc}
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exceptions\EbicsException
     */
    public function HPB(Bank $bank, User $user, KeyRing $keyRing, DateTime $dateTime = null): Response
    {
        $request = $this->requestFactory->buildHPB($bank, $user, $keyRing,$dateTime ?? new DateTime());
        $response = $this->requestMaker->post($request, $bank);

        $this->responseHandler->checkH004ReturnCode($request, $response);
        // Prepare decrypted OrderData.
        $orderData = $this->cryptService->decryptOrderData($keyRing, $this->responseHandler->retrieveOrderData($response));

        $response->addTransaction(Transaction::buildTransactionFromOrderData($orderData));
        $keyRing->setBankCertificateX($this->orderDataHandler->retrieveAuthenticationCertificate($orderData));
        $keyRing->setBankCertificateE($this->orderDataHandler->retrieveEncryptionCertificate($orderData));

        return $response;
    }

    /**
     * {@inheritdoc}
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exceptions\EbicsException
     */
    public function HPD(Bank $bank, User $user, KeyRing $keyRing, DateTime $dateTime = null): Response
    {
        $request = $this->requestFactory->buildHPD($bank, $user, $keyRing,$dateTime ?? new DateTime());
        $response = $this->requestMaker->post($request, $bank);

        $this->responseHandler->checkH004ReturnCode($request, $response);
        // TODO: Send Receipt transaction.
        $transaction = $this->responseHandler->retrieveTransaction($response);
        $response->addTransaction($transaction);
        // Prepare decrypted OrderData.
        $orderDataEncrypted = $this->responseHandler->retrieveOrderData($response);
        $orderData = $this->cryptService->decryptOrderData($keyRing, $orderDataEncrypted);
        $transaction->setOrderData($orderData);

        return $response;
    }

    /**
     * {@inheritdoc}
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exceptions\EbicsException
     */
    public function HKD(Bank $bank, User $user, KeyRing $keyRing,  DateTime $dateTime = null): Response
    {
        $request = $this->requestFactory->buildHKD($bank, $user, $keyRing,$dateTime ?? new DateTime());
        $response = $this->requestMaker->post($request, $bank);

        $this->responseHandler->checkH004ReturnCode($request, $response);
        // TODO: Send Receipt transaction.
        $transaction = $this->responseHandler->retrieveTransaction($response);
        $response->addTransaction($transaction);
        // Prepare decrypted OrderData.
        $orderDataEncrypted = $this->responseHandler->retrieveOrderData($response);
        $orderData = $this->cryptService->decryptOrderData($keyRing, $orderDataEncrypted);
        $transaction->setOrderData($orderData);

        return $response;
    }

    /**
     * {@inheritdoc}
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exceptions\EbicsException
     */
    public function HTD(Bank $bank, User $user, KeyRing $keyRing, DateTime $dateTime = null): Response
    {
        $request = $this->requestFactory->buildHTD($bank, $user, $keyRing, $dateTime ?? new DateTime());
        $response = $this->requestMaker->post($request, $bank);

        $this->responseHandler->checkH004ReturnCode($request, $response);
        // TODO: Send Receipt transaction.
        $transaction = $this->responseHandler->retrieveTransaction($response);
        $response->addTransaction($transaction);
        // Prepare decrypted OrderData.
        $orderDataEncrypted = $this->responseHandler->retrieveOrderData($response);
        $orderData = $this->cryptService->decryptOrderData($keyRing, $orderDataEncrypted);
        $transaction->setOrderData($orderData);

        return $response;
    }

    /**
     * {@inheritdoc}
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exceptions\EbicsException
     * @throws Exceptions\EbicsResponseException
     * @throws Exceptions\NoDownloadDataAvailableException
     */
    public function FDL(Bank $bank, User $user, KeyRing $keyRing, string $fileInfo, string $format = 'plain', string $countryCode = 'FR', DateTime $dateTime = null, DateTime $startDateTime = null, DateTime $endDateTime = null): Response
    {
        $request = $this->requestFactory->buildFDL($bank, $user, $keyRing, $dateTime ?? new DateTime(), $fileInfo, $countryCode, $startDateTime, $endDateTime);
        $response = $this->requestMaker->post($request, $bank);

        $this->responseHandler->checkH004ReturnCode($request, $response);

        // TODO: Send Transfer transaction.
        $transaction = $this->responseHandler->retrieveTransaction($response);
        $response->addTransaction($transaction);

        // Prepare decrypted datas.
        $orderDataEncrypted = $this->responseHandler->retrieveOrderData($response);
        switch ($format) {
            case 'plain':
                $orderDataContent = $this->cryptService->decryptOrderDataContent($keyRing, $orderDataEncrypted);
                $transaction->setPlainOrderData($orderDataContent);
                break;
            case 'xml':
            default:
                $orderData = $this->cryptService->decryptOrderData($keyRing, $orderDataEncrypted);
                $transaction->setOrderData($orderData);
                break;
        }

        return $response;
    }

    public function transferReceipt(Bank $bank, KeyRing $keyRing, Response $response, bool $acknowledged = true) : Response
    {
        $lastTransaction = $response->getLastTransaction();
        if (null === $lastTransaction) {
            throw new EbicsException('There is no transactions to mark as received');
        }

        $request = $this->requestFactory->buildTransferReceipt($bank, $keyRing, $lastTransaction, $acknowledged);
        $response = $this->requestMaker->post($request, $bank);

        try {
            $this->responseHandler->checkH004ReturnCode($request, $response);
        } catch (DownloadPostprocessDoneException $exception) {
            //EBICS_DOWNLOAD_POSTPROCESS_DONE, means transfer is OK (...)
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exceptions\EbicsException
     */
    public function HAA(Bank $bank, User $user, KeyRing $keyRing, DateTime $dateTime = null): Response
    {
        $request = $this->requestFactory->buildHAA($bank, $user, $keyRing,$dateTime ?? new DateTime());
        $response = $this->requestMaker->post($request, $bank);

        $this->responseHandler->checkH004ReturnCode($request, $response);

        return $response;
    }

    /**
     * {@inheritdoc}
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exceptions\EbicsException
     */
    public function VMK(Bank $bank, User $user, KeyRing $keyRing, DateTime $dateTime = null, DateTime $startDateTime = null, DateTime $endDateTime = null): Response
    {
        $request = $this->requestFactory->buildVMK($bank, $user, $keyRing,$dateTime ?? new DateTime(), $startDateTime, $endDateTime);
        $response = $this->requestMaker->post($request, $bank);

        $this->responseHandler->checkH004ReturnCode($request, $response);

        return $response;
    }

    /**
     * {@inheritdoc}
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exceptions\EbicsException
     */
    public function STA(Bank $bank, User $user, KeyRing $keyRing, DateTime $dateTime = null, DateTime $startDateTime = null, DateTime $endDateTime = null): Response
    {
        $request = $this->requestFactory->buildSTA($bank, $user, $keyRing,$dateTime ?? new DateTime(), $startDateTime, $endDateTime);
        $response = $this->requestMaker->post($request, $bank);

        $this->responseHandler->checkH004ReturnCode($request, $response);

        return $response;
    }
}

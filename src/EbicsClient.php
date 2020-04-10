<?php

namespace AndrewSvirin\Ebics;

use AndrewSvirin\Ebics\Contracts\EbicsClientInterface;
use AndrewSvirin\Ebics\Contracts\EbicsResponseExceptionInterface;
use AndrewSvirin\Ebics\Exceptions\NoDownloadDataAvailableException;
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
     * Constructor.
     */
    public function __construct(Bank $bank, User $user, KeyRing $keyRing)
    {
        $this->bank = $bank;
        $this->user = $user;
        $this->keyRing = $keyRing;
        $this->requestFactory = new RequestHandler($bank, $user, $keyRing);
        $this->orderDataHandler = new OrderDataHandler($bank, $user, $keyRing);
        $this->responseHandler = new ResponseHandler();
    }

    /**
     * Make request to bank server.
     *
     * @throws TransportExceptionInterface
     */
    public function post(Request $request): ResponseInterface
    {
        $body = $request->getContent();
        //dump($body);
        $httpClient = HttpClient::create();
        $response = $httpClient->request('POST', $this->bank->getUrl(), [
            'headers' => [
                'Content-Type' => 'text/xml; charset=ISO-8859-1',
            ],
            'body' => $body,
            'verify_peer' => false,
            'verify_host' => false,
        ]);
        //dump($response->getContent());

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
     * {@inheritdoc}
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function INI(DateTime $dateTime = null): Response
    {
        if (null === $dateTime) {
            $dateTime = DateTime::createFromFormat('U', time());
        }
        $certificateA = CertificateFactory::generateCertificateAFromKeys(CryptService::generateKeys($this->keyRing), $this->bank->isCertified());
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
     * {@inheritdoc}
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function HIA(DateTime $dateTime = null): Response
    {
        if (null === $dateTime) {
            $dateTime = DateTime::createFromFormat('U', time());
        }
        $certificateE = CertificateFactory::generateCertificateEFromKeys(CryptService::generateKeys($this->keyRing), $this->bank->isCertified());
        $certificateX = CertificateFactory::generateCertificateXFromKeys(CryptService::generateKeys($this->keyRing), $this->bank->isCertified());
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
     * {@inheritdoc}
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
            $dateTime = DateTime::createFromFormat('U', time());
        }
        $request = $this->requestFactory->buildHPB($dateTime);
        $hostResponse = $this->post($request);
        $hostResponseContent = $hostResponse->getContent();
        $response = new Response();
        $response->loadXML($hostResponseContent);

        $this->checkH004ReturnCode($request, $response);
        // Prepare decrypted OrderData.
        $orderDataEncrypted = $this->responseHandler->retrieveOrderData($response);
        $orderData = CryptService::decryptOrderData($this->keyRing, $orderDataEncrypted);
        $response->addTransaction(TransactionFactory::buildTransactionFromOrderData($orderData));
        $certificateX = $this->orderDataHandler->retrieveAuthenticationCertificate($orderData);
        $certificateE = $this->orderDataHandler->retrieveEncryptionCertificate($orderData);
        $this->keyRing->setBankCertificateX($certificateX);
        $this->keyRing->setBankCertificateE($certificateE);

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
    public function HPD(DateTime $dateTime = null): Response
    {
        if (null === $dateTime) {
            $dateTime = DateTime::createFromFormat('U', time());
        }
        $request = $this->requestFactory->buildHPD($dateTime);
        $hostResponse = $this->post($request);
        $hostResponseContent = $hostResponse->getContent();
        $response = new Response();
        $response->loadXML($hostResponseContent);

        $this->checkH004ReturnCode($request, $response);
        // TODO: Send Receipt transaction.
        $transaction = $this->responseHandler->retrieveTransaction($response);
        $response->addTransaction($transaction);
        // Prepare decrypted OrderData.
        $orderDataEncrypted = $this->responseHandler->retrieveOrderData($response);
        $orderData = CryptService::decryptOrderData($this->keyRing, $orderDataEncrypted);
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
    public function HKD(DateTime $dateTime = null): Response
    {
        if (null === $dateTime) {
            $dateTime = DateTime::createFromFormat('U', time());
        }
        $request = $this->requestFactory->buildHKD($dateTime);
        $hostResponse = $this->post($request);
        $hostResponseContent = $hostResponse->getContent();
        $response = new Response();
        $response->loadXML($hostResponseContent);

        $this->checkH004ReturnCode($request, $response);
        // TODO: Send Receipt transaction.
        $transaction = $this->responseHandler->retrieveTransaction($response);
        $response->addTransaction($transaction);
        // Prepare decrypted OrderData.
        $orderDataEncrypted = $this->responseHandler->retrieveOrderData($response);
        $orderData = CryptService::decryptOrderData($this->keyRing, $orderDataEncrypted);
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
    public function HTD(DateTime $dateTime = null): Response
    {
        if (null === $dateTime) {
            $dateTime = DateTime::createFromFormat('U', time());
        }

        $request = $this->requestFactory->buildHTD($dateTime);
        $hostResponse = $this->post($request);
        $hostResponseContent = $hostResponse->getContent();
        $response = new Response();
        $response->loadXML($hostResponseContent);

        $this->checkH004ReturnCode($request, $response);
        // TODO: Send Receipt transaction.
        $transaction = $this->responseHandler->retrieveTransaction($response);
        $response->addTransaction($transaction);
        // Prepare decrypted OrderData.
        $orderDataEncrypted = $this->responseHandler->retrieveOrderData($response);
        $orderData = CryptService::decryptOrderData($this->keyRing, $orderDataEncrypted);
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
    public function FDL(string $fileInfo, string $countryCode = 'FR', DateTime $dateTime = null, DateTime $startDateTime = null, DateTime $endDateTime = null): Response
    {
        if (null === $dateTime) {
            $dateTime = DateTime::createFromFormat('U', time());
        }

        $request = $this->requestFactory->buildFDL($dateTime, $fileInfo, $countryCode, $startDateTime, $endDateTime);
        $hostResponse = $this->post($request);
        $hostResponseContent = $hostResponse->getContent();
        /*
                $hostResponseContent = <<<EOF
        <?xml version="1.0" encoding="UTF-8"?>
        <ebicsResponse xmlns="urn:org:ebics:H004" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" Version="H004" Revision="1" xsi:schemaLocation="urn:org:ebics:H004 ebics_response_H004.xsd">
            <header authenticate="true">
                <static>
                    <TransactionID>520F251CBA36F4852B84BF8C75540B05</TransactionID>
                    <NumSegments>1</NumSegments>
                </static>
                <mutable>
                    <TransactionPhase>Initialisation</TransactionPhase>
                    <SegmentNumber lastSegment="true">1</SegmentNumber>
                    <ReturnCode>000000</ReturnCode>
                    <ReportText>[EBICS_OK] OK</ReportText>
                </mutable>
            </header>
            <AuthSignature>
                <ds:SignedInfo>
                    <ds:CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/>
                    <ds:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/>
                    <ds:Reference URI="#xpointer(//*[@authenticate='true'])">
                        <ds:Transforms>
                            <ds:Transform Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/>
                        </ds:Transforms>
                        <ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>
                        <ds:DigestValue>pQXSMDUv7ku8htldih4VAAdxlE4aB5Vbu+ihTH86XjY=</ds:DigestValue>
                    </ds:Reference>
                </ds:SignedInfo>
                <ds:SignatureValue>j2x3sw9ZY5aj9PQnViQZWh6x/zpVzoRT3YZ7YJuQmgtqtLxcLWFDS6aj3+Pu78LEQzwcjne7wodePxqLy4XpbOH1hsfk4/hoPa03f7vGnyHLJRNCmk12GiKKvp71oUWPy32/0nAcUReZKewBD1cVZ4NzlRLTdhIRfBTz+fb7uVrwvFY5d70yD44AqoSMmx2N88gC0UCDdrW1oAexsuIxM1JJ6NUDqptZzCL4izpKvfvM5IO+/UaaQrqrf9aTrabmtek/hmnelM0H2+3NyhqsMt3M8uQt18JRffvVlKCyTOE1YJx1JRRhzLMSbR2iK8iNLGReFbtzFyoX5VxObt8yGQ==</ds:SignatureValue>
            </AuthSignature>
            <body>
                <DataTransfer>
                    <DataEncryptionInfo authenticate="true">
                        <EncryptionPubKeyDigest Version="E002" Algorithm="http://www.w3.org/2001/04/xmlenc#sha256">Mh1mHtEtQ3R6ipob/yXAxZDNMnN1VC35YZR6ZdZtO2k=</EncryptionPubKeyDigest>
                        <TransactionKey>B2u+3wy/lEhMaOpn6u9jtb1kS1WZDwLTlgfdx+2+iF3oO/lY+qn08iSGbsGCiwo3QsPr8xIiO4ywRUWWHbh99GhGGzUgFeKVo2NzyQ4b1tySGnETbsbMbcoiHM7TeawYsMasq6cBn66PzpPl/BOANfgQSQfNVxcI1Cqhxfh7Hnv4+SviZWVm+kniursZpS2yeq02DrMxqo9JIUu20slGDCl5lwX7W+hvWPn7VdQ1/WZLtR25T330KtEemERhe/jsH2KdDYWQuXISRm1nSWzE96Nl7KGS1/V4GTRgwhh7eP19jNO6EiK+WgA/Gmijhi9bhQTwBvkFWFoyFh0h7kIndQ==</TransactionKey>
                    </DataEncryptionInfo>
                    <OrderData>4ZkdKoGH3AKAg2R+x9r3JBP4zhm0of3NPPgpcg3HnLLGle442oMpetUkduGfK5UkMEXe9DFUH/A1Jc4cvt8nJPA7oUnn7E0erSB2Pmb68cg=</OrderData>
                </DataTransfer>
                <ReturnCode authenticate="true">000000</ReturnCode>
                <TimestampBankParameter authenticate="true">2016-02-17T12:18:48.548Z</TimestampBankParameter>
            </body>
        </ebicsResponse>
        EOF;
        */
        $response = new Response();
        $response->loadXML($hostResponseContent);

        $this->checkH004ReturnCode($request, $response);

        //EBICS_NO_DOWNLOAD_DATA_AVAILABLE
        if ('090005' === $this->responseHandler->retrieveH004BodyOrHeaderReturnCode($response)) {
            throw new NoDownloadDataAvailableException($this->responseHandler->retrieveH004ReportText($response));
        }

        $transaction = $this->responseHandler->retrieveTransaction($response);
        $response->addTransaction($transaction);

        // Prepare decrypted OrderData.
        $orderDataEncrypted = $this->responseHandler->retrieveOrderData($response);
        $orderDataContent = CryptService::decryptOrderDataContent($this->keyRing, $orderDataEncrypted);
        $transaction->setPlainOrderData($orderDataContent);

        return $response;
    }

    public function fileAcknowledgement(Transaction $transaction)
    {
        $request = $this->requestFactory->buildFileAcknowledgement($transaction);
        $hostResponse = $this->post($request);
        $hostResponseContent = $hostResponse->getContent();
        $response = new Response();
        $response->loadXML($hostResponseContent);

        try {
            $this->checkH004ReturnCode($request, $response);
        } catch (EbicsResponseExceptionInterface $exception) {
            //EBICS_DOWNLOAD_POSTPROCESS_DONE, means download is OK
            if ('011000' !== $exception->getResponseCode()) {
                throw $exception;
            }
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
    public function HAA(DateTime $dateTime = null): Response
    {
        if (null === $dateTime) {
            $dateTime = DateTime::createFromFormat('U', time());
        }
        $request = $this->requestFactory->buildHAA($dateTime);
        $hostResponse = $this->post($request);
        $hostResponseContent = $hostResponse->getContent();
        $response = new Response();
        $response->loadXML($hostResponseContent);

        $this->checkH004ReturnCode($request, $response);

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
    public function VMK(DateTime $dateTime = null, DateTime $startDateTime = null, DateTime $endDateTime = null): Response
    {
        if (null === $dateTime) {
            $dateTime = DateTime::createFromFormat('U', time());
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
     * {@inheritdoc}
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exceptions\EbicsException
     */
    public function STA(DateTime $dateTime = null, DateTime $startDateTime = null, DateTime $endDateTime = null): Response
    {
        if (null === $dateTime) {
            $dateTime = DateTime::createFromFormat('U', time());
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
     * @throws EbicsResponseExceptionInterface
     */
    private function checkH004ReturnCode(Request $request, Response $response): void
    {
        $errorCode = $this->responseHandler->retrieveH004BodyOrHeaderReturnCode($response);

        if ('00000' === $errorCode) {
            return;
        }

        $reportText = $this->responseHandler->retrieveH004ReportText($response);
        throw EbicsExceptionFactory::buildExceptionFromCode($errorCode, $reportText, $request, $response);
    }

    /**
     * @throws EbicsResponseExceptionInterface
     */
    private function checkH000ReturnCode(Request $request, Response $response): void
    {
        $errorCode = $this->responseHandler->retrieveH000ReturnCode($response);

        if ('00000' === $errorCode) {
            return;
        }

        $reportText = $this->responseHandler->retrieveH000ReportText($response);
        throw EbicsExceptionFactory::buildExceptionFromCode($errorCode, $reportText, $request, $response);
    }
}

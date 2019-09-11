<?php

namespace AndrewSvirin\Ebics;

use AndrewSvirin\Ebics\contracts\EbicsClientInterface;
use AndrewSvirin\Ebics\factories\CertificateFactory;
use AndrewSvirin\Ebics\factories\TransactionFactory;
use AndrewSvirin\Ebics\handlers\AuthSignatureHandler;
use AndrewSvirin\Ebics\handlers\BodyHandler;
use AndrewSvirin\Ebics\handlers\HeaderHandler;
use AndrewSvirin\Ebics\handlers\HostHandler;
use AndrewSvirin\Ebics\handlers\OrderDataHandler;
use AndrewSvirin\Ebics\handlers\RequestHandler;
use AndrewSvirin\Ebics\handlers\ResponseHandler;
use AndrewSvirin\Ebics\models\Bank;
use AndrewSvirin\Ebics\models\KeyRing;
use AndrewSvirin\Ebics\models\OrderData;
use AndrewSvirin\Ebics\models\Request;
use AndrewSvirin\Ebics\models\Response;
use AndrewSvirin\Ebics\models\User;
use AndrewSvirin\Ebics\services\CryptService;
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
    * @var Bank
    */
   private $bank;

   /**
    * An EbicsUser instance.
    * @var User
    */
   private $user;

   /**
    * @var RequestHandler
    */
   private $requestHandler;

   /**
    * @var HeaderHandler
    */
   private $headerHandler;

   /**
    * @var BodyHandler
    */
   private $bodyHandler;

   /**
    * @var KeyRing
    */
   private $keyRing;

   /**
    * @var OrderDataHandler
    */
   private $orderDataHandler;

   /**
    * @var AuthSignatureHandler
    */
   private $authSignatureHandler;

   /**
    * @var ResponseHandler
    */
   private $responseHandler;

   /**
    * @var HostHandler
    */
   private $hostHandler;

   /**
    * @var CryptService
    */
   private $cryptService;

   /**
    * Constructor.
    * @param Bank $bank
    * @param User $user
    * @param KeyRing $keyRing
    */
   public function __construct(Bank $bank, User $user, KeyRing $keyRing)
   {
      $this->bank = $bank;
      $this->user = $user;
      $this->keyRing = $keyRing;
      $this->cryptService = new CryptService($keyRing);
      $this->requestHandler = new RequestHandler();
      $this->headerHandler = new HeaderHandler($bank, $user, $keyRing, $this->cryptService);
      $this->bodyHandler = new BodyHandler();
      $this->orderDataHandler = new OrderDataHandler($bank, $user, $keyRing);
      $this->authSignatureHandler = new AuthSignatureHandler($this->cryptService);
      $this->responseHandler = new ResponseHandler();
      $this->hostHandler = new HostHandler($bank);
   }

   /**
    * @param string $body
    * @return ResponseInterface
    * @throws TransportExceptionInterface
    */
   private function post($body): ResponseInterface
   {
      $httpClient = HttpClient::create();
      $response = $httpClient->request('POST', $this->bank->getUrl(), [
         'headers' => [
            'Content-Type' => 'text/xml; charset=ISO-8859-1',
         ],
         'body' => $body,
         'verify_peer' => false,
         'verify_host' => false,
      ]);
      return $response;
   }

   /**
    * {@inheritdoc}
    * @throws ClientExceptionInterface
    * @throws RedirectionExceptionInterface
    * @throws ServerExceptionInterface
    * @throws TransportExceptionInterface
    */
   public function HEV(): Response
   {
      $request = new Request();
      $xmlRequest = $this->requestHandler->handleHEV($request);
      $this->hostHandler->handle($request, $xmlRequest);
      $requestContent = $request->getContent();
      $hostResponse = $this->post($requestContent);
      $hostResponseContent = $hostResponse->getContent();
      $response = new Response();
      $response->loadXML($hostResponseContent);
      return $response;
   }

   /**
    * {@inheritdoc}
    * @throws ClientExceptionInterface
    * @throws RedirectionExceptionInterface
    * @throws ServerExceptionInterface
    * @throws TransportExceptionInterface
    */
   public function INI(DateTime $dateTime = null): Response
   {
      if (null === $dateTime)
      {
         $dateTime = DateTime::createFromFormat('U', time());
      }
      $certificateA = CertificateFactory::generateCertificateAFromKeys($this->cryptService->generateKeys());
      // Order data.
      $orderData = new OrderData();
      $this->orderDataHandler->handleINI($orderData, $certificateA, $dateTime);
      $orderDataContent = $orderData->getContent();
      // Wrapper for request Order data.
      $request = new Request();
      $xmlRequest = $this->requestHandler->handleUnsecured($request);
      $this->headerHandler->handleINI($request, $xmlRequest);
      $this->bodyHandler->handle($request, $xmlRequest, $orderDataContent);
      $requestContent = $request->getContent();
      $hostResponse = $this->post($requestContent);
      $hostResponseContent = $hostResponse->getContent();
      $response = new Response();
      $response->loadXML($hostResponseContent);
      if ('000000' === $this->responseHandler->retrieveH004ReturnCode($response))
      {
         $this->keyRing->setUserCertificateA($certificateA);
      }
      return $response;
   }

   /**
    * {@inheritdoc}
    * @throws ClientExceptionInterface
    * @throws RedirectionExceptionInterface
    * @throws ServerExceptionInterface
    * @throws TransportExceptionInterface
    */
   public function HIA(DateTime $dateTime = null): Response
   {
      if (null === $dateTime)
      {
         $dateTime = DateTime::createFromFormat('U', time());
      }
      $certificateE = CertificateFactory::generateCertificateEFromKeys($this->cryptService->generateKeys());
      $certificateX = CertificateFactory::generateCertificateXFromKeys($this->cryptService->generateKeys());
      // Order data.
      $orderData = new OrderData();
      $this->orderDataHandler->handleHIA($orderData, $certificateE, $certificateX, $dateTime);
      $orderDataContent = $orderData->getContent();
      // Wrapper for request Order data.
      $request = new Request();
      $xmlRequest = $this->requestHandler->handleUnsecured($request);
      $this->headerHandler->handleHIA($request, $xmlRequest);
      $this->bodyHandler->handle($request, $xmlRequest, $orderDataContent);
      $requestContent = $request->getContent();
      $hostResponse = $this->post($requestContent);
      $hostResponseContent = $hostResponse->getContent();
      $response = new Response();
      $response->loadXML($hostResponseContent);
      if ('000000' === $this->responseHandler->retrieveH004ReturnCode($response))
      {
         $this->keyRing->setUserCertificateE($certificateE);
         $this->keyRing->setUserCertificateX($certificateX);
      }
      return $response;
   }

   /**
    * {@inheritdoc}
    * @throws ClientExceptionInterface
    * @throws RedirectionExceptionInterface
    * @throws ServerExceptionInterface
    * @throws TransportExceptionInterface
    * @throws exceptions\EbicsException
    */
   public function HPB(DateTime $dateTime = null): Response
   {
      if (null === $dateTime)
      {
         $dateTime = DateTime::createFromFormat('U', time());
      }
      $request = new Request();
      $xmlRequest = $this->requestHandler->handleNoPubKeyDigests($request);
      $this->headerHandler->handleHPB($request, $xmlRequest, $dateTime);
      $this->authSignatureHandler->handle($request, $xmlRequest);
      $this->bodyHandler->handleEmpty($request, $xmlRequest);
      $requestContent = $request->getContent();
      $hostResponse = $this->post($requestContent);
      $hostResponseContent = $hostResponse->getContent();
      $response = new Response();
      $response->loadXML($hostResponseContent);
      if ('000000' === $this->responseHandler->retrieveH004ReturnCode($response))
      {
         // Prepare decrypted OrderData.
         $orderDataEncrypted = $this->responseHandler->retrieveOrderData($response);
         $orderData = $this->cryptService->decryptOrderData($orderDataEncrypted);
         $response->addTransaction(TransactionFactory::buildTransactionFromOrderData($orderData));
         $certificateX = $this->orderDataHandler->retrieveAuthenticationCertificate($orderData);
         $certificateE = $this->orderDataHandler->retrieveEncryptionCertificate($orderData);
         $this->keyRing->setBankCertificateX($certificateX);
         $this->keyRing->setBankCertificateE($certificateE);
      }
      return $response;
   }

   /**
    * {@inheritdoc}
    * @throws ClientExceptionInterface
    * @throws RedirectionExceptionInterface
    * @throws ServerExceptionInterface
    * @throws TransportExceptionInterface
    * @throws exceptions\EbicsException
    */
   public function HPD(DateTime $dateTime = null): Response
   {
      if (null === $dateTime)
      {
         $dateTime = DateTime::createFromFormat('U', time());
      }
      $request = new Request();
      $xmlRequest = $this->requestHandler->handleSecured($request);
      $this->headerHandler->handleHPD($request, $xmlRequest, $dateTime);
      $this->authSignatureHandler->handle($request, $xmlRequest);
      $this->bodyHandler->handleEmpty($request, $xmlRequest);
      $requestContent = $request->getContent();
      $hostResponse = $this->post($requestContent);
      $hostResponseContent = $hostResponse->getContent();
      $response = new Response();
      $response->loadXML($hostResponseContent);

      // TODO: Send Receipt transaction.
      $transaction = $this->responseHandler->retrieveTransaction($response);
      $response->addTransaction($transaction);

      // Prepare decrypted OrderData.
      $orderDataEncrypted = $this->responseHandler->retrieveOrderData($response);
      $orderData = $this->cryptService->decryptOrderData($orderDataEncrypted);
      $transaction->setOrderData($orderData);
      return $response;
   }

   /**
    * {@inheritdoc}
    * @throws ClientExceptionInterface
    * @throws RedirectionExceptionInterface
    * @throws ServerExceptionInterface
    * @throws TransportExceptionInterface
    * @throws exceptions\EbicsException
    */
   public function HAA(DateTime $dateTime = null): Response
   {
      if (null === $dateTime)
      {
         $dateTime = DateTime::createFromFormat('U', time());
      }
      $request = new Request();
      $xmlRequest = $this->requestHandler->handleSecured($request);
      $this->headerHandler->handleHAA($request, $xmlRequest, $dateTime);
      $this->authSignatureHandler->handle($request, $xmlRequest);
      $this->bodyHandler->handleEmpty($request, $xmlRequest);
      $requestContent = $request->getContent();
      $hostResponse = $this->post($requestContent);
      $hostResponseContent = $hostResponse->getContent();
      $response = new Response();
      $response->loadXML($hostResponseContent);
      return $response;
   }

   /**
    * {@inheritdoc}
    * @throws ClientExceptionInterface
    * @throws RedirectionExceptionInterface
    * @throws ServerExceptionInterface
    * @throws TransportExceptionInterface
    * @throws exceptions\EbicsException
    */
   public function VMK(DateTime $dateTime = null, DateTime $startDateTime = null, DateTime $endDateTime = null): Response
   {
      if (null === $dateTime)
      {
         $dateTime = DateTime::createFromFormat('U', time());
      }
      $request = new Request();
      $xmlRequest = $this->requestHandler->handleSecured($request);
      $this->headerHandler->handleVMK($request, $xmlRequest, $dateTime, $startDateTime, $endDateTime);
      $this->authSignatureHandler->handle($request, $xmlRequest);
      $this->bodyHandler->handleEmpty($request, $xmlRequest);
      $requestContent = $request->getContent();
      $hostResponse = $this->post($requestContent);
      $hostResponseContent = $hostResponse->getContent();
      $response = new Response();
      $response->loadXML($hostResponseContent);
      return $response;
   }

   /**
    * {@inheritdoc}
    * @throws ClientExceptionInterface
    * @throws RedirectionExceptionInterface
    * @throws ServerExceptionInterface
    * @throws TransportExceptionInterface
    * @throws exceptions\EbicsException
    */
   public function STA(DateTime $dateTime = null, DateTime $startDateTime = null, DateTime $endDateTime = null): Response
   {
      if (null === $dateTime)
      {
         $dateTime = DateTime::createFromFormat('U', time());
      }
      $request = new Request();
      $xmlRequest = $this->requestHandler->handleSecured($request);
      $this->headerHandler->handleSTA($request, $xmlRequest, $dateTime, $startDateTime, $endDateTime);
      $this->authSignatureHandler->handle($request, $xmlRequest);
      $this->bodyHandler->handleEmpty($request, $xmlRequest);
      $requestContent = $request->getContent();
      $hostResponse = $this->post($requestContent);
      $hostResponseContent = $hostResponse->getContent();
      $response = new Response();
      $response->loadXML($hostResponseContent);
      return $response;
   }

}

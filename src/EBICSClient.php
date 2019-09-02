<?php

namespace AndrewSvirin\Ebics;

use AndrewSvirin\Ebics\factories\CertificateFactory;
use AndrewSvirin\Ebics\handlers\BodyHandler;
use AndrewSvirin\Ebics\handlers\HeaderHandler;
use AndrewSvirin\Ebics\handlers\OrderDataHandler;
use AndrewSvirin\Ebics\handlers\RequestHandler;
use AndrewSvirin\Ebics\models\Bank;
use AndrewSvirin\Ebics\models\KeyRing;
use AndrewSvirin\Ebics\models\OrderData;
use AndrewSvirin\Ebics\models\Request;
use AndrewSvirin\Ebics\models\Response;
use AndrewSvirin\Ebics\models\User;
use DOMDocument;
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
class EBICSClient
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
      $this->requestHandler = new RequestHandler();
      $this->headerHandler = new HeaderHandler($bank, $user);
      $this->bodyHandler = new BodyHandler();
      $this->orderDataHandler = new OrderDataHandler($user);
   }

   /**
    * Getter for bank.
    * @return Bank
    */
   public function getBank()
   {
      return $this->bank;
   }

   /**
    * Getter for user
    * @return User
    */
   public function getUser()
   {
      return $this->user;
   }

   /**
    * Getter for keyRing
    * @return KeyRing
    */
   public function getKeyRing()
   {
      return $this->keyRing;
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
    * Make INI request.
    * Setup A006 certificates to key ring.
    * @return Response
    * @throws ClientExceptionInterface
    * @throws RedirectionExceptionInterface
    * @throws ServerExceptionInterface
    * @throws TransportExceptionInterface
    */
   public function INI(): Response
   {
      $certificateA = CertificateFactory::generateCertificateA();
      // Order data.
      $orderData = new OrderData();
      $this->orderDataHandler->handleINI($orderData, $certificateA);
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
      $this->keyRing->setCertificateA($certificateA);
      return $response;
   }

   /**
    * Make HIA request.
    * Setup E002 and X002 certificates to key ring.
    * @return Response
    * @throws ClientExceptionInterface
    * @throws RedirectionExceptionInterface
    * @throws ServerExceptionInterface
    * @throws TransportExceptionInterface
    */
   public function HIA(): Response
   {
      $certificateE = CertificateFactory::generateCertificateE();
      $certificateX = CertificateFactory::generateCertificateX();
      // Order data.
      $orderData = new OrderData();
      $this->orderDataHandler->handleHIA($orderData, $certificateE, $certificateX);
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
      $this->keyRing->setCertificateE($certificateE);
      $this->keyRing->setCertificateX($certificateX);
      return $response;
   }

   public function SPR()
   {

   }

   /**
    * Downloads the bank account statement in SWIFT format (MT940).
    * @param int $start The start date of requested transactions.
    * @param int $end The end date of requested transactions.
    * @param boolean $parsed Flag whether the received MT940 message should be
    * parsed and returned as a dictionary or not.
    * @return
    */
   public function STA($start = NULL, $end = NULL, $parsed = FALSE)
   {
      return '';
   }

   /**
    * Downloads the interim transaction report in SWIFT format (MT942).
    * @param int $start The start date of requested transactions.
    * @param int $end The end date of requested transactions.
    * @param boolean $parsed Flag whether the received MT940 message should be
    * parsed and returned as a dictionary or not.
    * @return Response
    * @throws \Comodojo\Exception\HttpException
    * @throws exceptions\EbicsException
    * @throws \Exception
    */
   public function VMK($start = NULL, $end = NULL, $parsed = FALSE)
   {
      $domTree = new DOMDocument();

      // Add OrderDetails.
      $xmlOrderDetails = $domTree->createElement('OrderDetails');
      $domTree->appendChild($xmlOrderDetails);

      // Add OrderType.
      $xmlOrderType = $domTree->createElement('OrderType');
      $xmlOrderType->nodeValue = 'VMK';
      $xmlOrderDetails->appendChild($xmlOrderType);

      // Add OrderAttribute.
      $xmlOrderAttribute = $domTree->createElement('OrderAttribute');
      $xmlOrderAttribute->nodeValue = 'DZHNN';
      $xmlOrderDetails->appendChild($xmlOrderAttribute);

      // Add StandardOrderParams.
      $xmlStandardOrderParams = $domTree->createElement('StandardOrderParams');
      $xmlOrderDetails->appendChild($xmlStandardOrderParams);

      if ($start != NULL && $end != NULL)
      {
         // Add DateRange.
         $xmlDateRange = $domTree->createElement('DateRange');
         $xmlStandardOrderParams->appendChild($xmlDateRange);

         // Add Start.
         $xmlStart = $domTree->createElement('Start');
         $xmlStart->nodeValue = $start;
         $xmlDateRange->appendChild($xmlStart);
         // Add End.
         $xmlEnd = $domTree->createElement('End');
         $xmlEnd->nodeValue = $end;
         $xmlDateRange->appendChild($xmlEnd);
      }

      $request = new Request($this);
      $orderDetails = $domTree->getElementsByTagName('OrderDetails')->item(0);

      return $request->createRequest($orderDetails)->download();
   }

}

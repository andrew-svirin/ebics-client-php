<?php

namespace AndrewSvirin\Ebics\handlers;

use AndrewSvirin\Ebics\models\Bank;
use AndrewSvirin\Ebics\models\KeyRing;
use AndrewSvirin\Ebics\models\Transaction;
use AndrewSvirin\Ebics\models\User;
use AndrewSvirin\Ebics\services\CryptService;
use DateTime;
use DOMDocument;
use DOMElement;

/**
 * Class HeaderHandler manages header DOM elements.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class HeaderHandler
{

   const ORDER_TYPE_INI = 'INI';
   const ORDER_TYPE_HIA = 'HIA';
   const ORDER_TYPE_HPB = 'HPB';
   const ORDER_TYPE_VMK = 'VMK';
   const ORDER_TYPE_STA = 'STA';
   const ORDER_TYPE_HAA = 'HAA';
   const ORDER_TYPE_HPD = 'HPD';

   const ORDER_ATTRIBUTE_DZNNN = 'DZNNN';
   const ORDER_ATTRIBUTE_DZHNN = 'DZHNN';

   /**
    * @var User
    */
   private $user;

   /**
    * @var Bank
    */
   private $bank;

   /**
    * @var string
    */
   private $language;

   /**
    * @var string
    */
   private $securityMedium;

   /**
    * @var string
    */
   private $product;

   /**
    * @var KeyRing
    */
   private $keyRing;

   /**
    * @var CryptService
    */
   private $cryptService;

   public function __construct(Bank $bank, User $user, KeyRing $keyRing, CryptService $cryptService)
   {
      $this->bank = $bank;
      $this->user = $user;
      $this->keyRing = $keyRing;
      $this->cryptService = $cryptService;
      $this->language = 'de';
      $this->securityMedium = '0000';
      $this->product = 'Ebics client PHP';
   }

   /**
    * Add header for INI Request XML.
    * @param DOMDocument $xml
    * @param DOMElement $xmlRequest
    */
   public function handleINI(DOMDocument $xml, DOMElement $xmlRequest)
   {
      $this->handle(
         $xml,
         $xmlRequest,
         null,
         null,
         $this->handleOrderDetails(self::ORDER_TYPE_INI, self::ORDER_ATTRIBUTE_DZNNN),
         $this->handleMutable()
      );
   }

   /**
    * Add header for HIA Request XML.
    * @param DOMDocument $xml
    * @param DOMElement $xmlRequest
    */
   public function handleHIA(DOMDocument $xml, DOMElement $xmlRequest)
   {
      $this->handle(
         $xml,
         $xmlRequest,
         null,
         null,
         $this->handleOrderDetails(self::ORDER_TYPE_HIA, self::ORDER_ATTRIBUTE_DZNNN),
         $this->handleMutable()
      );
   }

   /**
    * Add header for HPB Request XML.
    * @param DOMDocument $xml
    * @param DOMElement $xmlRequest
    * @param DateTime $dateTime
    */
   public function handleHPB(DOMDocument $xml, DOMElement $xmlRequest, DateTime $dateTime)
   {
      $this->handle(
         $xml,
         $xmlRequest,
         $this->handleNonce($dateTime),
         null,
         $this->handleOrderDetails(self::ORDER_TYPE_HPB, self::ORDER_ATTRIBUTE_DZHNN),
         $this->handleMutable()
      );
   }

   /**
    * Add header for HAA Request XML.
    * @param DOMDocument $xml
    * @param DOMElement $xmlRequest
    * @param DateTime $dateTime
    */
   public function handleHAA(DOMDocument $xml, DOMElement $xmlRequest, DateTime $dateTime)
   {
      $this->handle(
         $xml,
         $xmlRequest,
         $this->handleNonce($dateTime),
         $this->handleBank(),
         $this->handleOrderDetails(self::ORDER_TYPE_HAA, self::ORDER_ATTRIBUTE_DZHNN, $this->handleStandardOrderParams()),
         $this->handleMutable($this->handleTransactionPhase(Transaction::PHASE_INITIALIZATION))
      );
   }

   /**
    * Add header for VMK Request XML.
    * @param DOMDocument $xml
    * @param DOMElement $xmlRequest
    * @param DateTime $dateTime
    * @param DateTime|null $startDateTime
    * @param DateTime|null $endDateTime
    */
   public function handleVMK(DOMDocument $xml, DOMElement $xmlRequest, DateTime $dateTime, DateTime $startDateTime = null, DateTime $endDateTime = null)
   {
      $this->handle(
         $xml,
         $xmlRequest,
         $this->handleNonce($dateTime),
         $this->handleBank(),
         $this->handleOrderDetails(
            self::ORDER_TYPE_VMK,
            self::ORDER_ATTRIBUTE_DZHNN,
            $this->handleStandardOrderParams($startDateTime, $endDateTime)
         ),
         $this->handleMutable($this->handleTransactionPhase(Transaction::PHASE_INITIALIZATION))
      );
   }

   /**
    * Add header for STA Request XML.
    * @param DOMDocument $xml
    * @param DOMElement $xmlRequest
    * @param $dateTime
    * @param DateTime $startDateTime
    * @param DateTime $endDateTime
    */
   public function handleSTA(DOMDocument $xml, DOMElement $xmlRequest, $dateTime, DateTime $startDateTime, DateTime $endDateTime)
   {
      $this->handle(
         $xml,
         $xmlRequest,
         $this->handleNonce($dateTime),
         $this->handleBank(),
         $this->handleOrderDetails(
            self::ORDER_TYPE_STA,
            self::ORDER_ATTRIBUTE_DZHNN,
            $this->handleStandardOrderParams($startDateTime, $endDateTime)
         ),
         $this->handleMutable($this->handleTransactionPhase(Transaction::PHASE_INITIALIZATION))
      );
   }

   /**
    * Add header for HPD Request XML.
    * @param DOMDocument $xml
    * @param DOMElement $xmlRequest
    * @param DateTime $dateTime
    */
   public function handleHPD(DOMDocument $xml, DOMElement $xmlRequest, DateTime $dateTime)
   {
      $this->handle(
         $xml,
         $xmlRequest,
         $this->handleNonce($dateTime),
         $this->handleBank(),
         $this->handleOrderDetails(
            self::ORDER_TYPE_HPD,
            self::ORDER_ATTRIBUTE_DZHNN,
            $this->handleStandardOrderParams()
         ),
         $this->handleMutable($this->handleTransactionPhase(Transaction::PHASE_INITIALIZATION))
      );
   }

   /**
    * Hook to add mutable information.
    * @param callable|null $transactionPhase
    * @return callable
    */
   private function handleMutable(callable $transactionPhase = null): callable
   {
      return function (DOMDocument $xml, DOMElement $xmlHeader) use ($transactionPhase)
      {
         // Add mutable to header.
         $xmlMutable = $xml->createElement('mutable');
         $xmlHeader->appendChild($xmlMutable);

         if (null !== $transactionPhase)
         {
            // Add TransactionPhase information to mutable.
            $transactionPhase($xml, $xmlMutable);
         }
      };
   }

   /**
    * Hook to add TransactionPhase information.
    * @param string $transactionPhase
    * @return callable
    */
   private function handleTransactionPhase(string $transactionPhase): callable
   {
      return function (DOMDocument $xml, DOMElement $xmlMutable) use ($transactionPhase)
      {
         // Add TransactionPhase to mutable.
         $xmlTransactionPhase = $xml->createElement('TransactionPhase');
         $xmlTransactionPhase->nodeValue = $transactionPhase;
         $xmlMutable->appendChild($xmlTransactionPhase);
      };
   }

   /**
    * Hook to add OrderDetails information.
    * @param string $orderType
    * @param string $orderAttribute
    * @param callable|null $orderParams
    * @return callable
    */
   private function handleOrderDetails(string $orderType, string $orderAttribute, callable $orderParams = null): callable
   {
      return function (DOMDocument $xml, DOMElement $xmlStatic) use ($orderType, $orderAttribute, $orderParams)
      {
         // Add OrderDetails to static.
         $xmlOrderDetails = $xml->createElement('OrderDetails');
         $xmlStatic->appendChild($xmlOrderDetails);

         // Add OrderType to OrderDetails.
         $xmlOrderType = $xml->createElement('OrderType');
         $xmlOrderType->nodeValue = $orderType;
         $xmlOrderDetails->appendChild($xmlOrderType);

         // Add OrderAttribute to OrderDetails.
         $xmlOrderAttribute = $xml->createElement('OrderAttribute');
         $xmlOrderAttribute->nodeValue = $orderAttribute;
         $xmlOrderDetails->appendChild($xmlOrderAttribute);

         if (null !== $orderParams)
         {
            // Add OrderParams information to OrderDetails.
            $orderParams($xml, $xmlOrderDetails);
         }
      };
   }

   /**
    * Hook to add StandardOrderParams information.
    * @param DateTime|null $startDateTime
    * @param DateTime|null $endDateTime
    * @return callable
    */
   private function handleStandardOrderParams(DateTime $startDateTime = null, DateTime $endDateTime = null): callable
   {
      return function (DOMDocument $xml, DOMElement $xmlOrderDetails) use ($startDateTime, $endDateTime)
      {
         // Add StandardOrderParams to OrderDetails.
         $xmlStandardOrderParams = $xml->createElement('StandardOrderParams');
         $xmlOrderDetails->appendChild($xmlStandardOrderParams);
         if (null !== $startDateTime && null !== $endDateTime)
         {
            // Add DateRange to StandardOrderParams.
            $xmlDateRange = $xml->createElement('DateRange');
            $xmlStandardOrderParams->appendChild($xmlDateRange);
            // Add Start to StandardOrderParams.
            $xmlStart = $xml->createElement('Start');
            $xmlStart->nodeValue = $startDateTime->format('Y-m-d');
            $xmlDateRange->appendChild($xmlStart);
            // Add End to StandardOrderParams.
            $xmlEnd = $xml->createElement('End');
            $xmlEnd->nodeValue = $endDateTime->format('Y-m-d');
            $xmlDateRange->appendChild($xmlEnd);
         }
      };
   }

   /**
    * Hook to add Nonce and Timestamp information.
    * @param DateTime $dateTime Stamped by date time and Nonce.
    * @return callable
    */
   private function handleNonce(DateTime $dateTime): callable
   {
      return function (DOMDocument $xml, DOMElement $xmlStatic) use ($dateTime)
      {
         // Add Nonce to static.
         $xmlNonce = $xml->createElement('Nonce');
         $xmlNonce->nodeValue = $this->cryptService->generateNonce();
         $xmlStatic->appendChild($xmlNonce);

         // Add TimeStamp to static.
         $xmlTimeStamp = $xml->createElement('Timestamp');
         $xmlTimeStamp->nodeValue = $dateTime->format('Y-m-d\TH:i:s\Z');
         $xmlStatic->appendChild($xmlTimeStamp);
      };
   }

   /**
    * Hook to add BankPubKeyDigests information.
    * @return callable
    */
   private function handleBank(): callable
   {
      $keyRing = $this->keyRing;
      $cryptService = $this->cryptService;
      return function (DOMDocument $xml, DOMElement $xmlStatic) use ($keyRing, $cryptService)
      {
         // Add BankPubKeyDigests to static.
         $xmlBankPubKeyDigests = $xml->createElement('BankPubKeyDigests');
         $xmlStatic->appendChild($xmlBankPubKeyDigests);

         // Add Authentication to BankPubKeyDigests.
         $xmlAuthentication = $xml->createElement('Authentication');
         $xmlAuthentication->setAttribute('Version', $keyRing->getBankCertificateXVersion());
         $xmlAuthentication->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmlenc#sha256');
         $certificateXDigest = $this->cryptService->calculateDigest(
            $keyRing->getBankCertificateX()->toX509()->getPublicKey()->exponent->toHex(),
            $keyRing->getBankCertificateX()->toX509()->getPublicKey()->modulus->toHex()
         );
         $xmlAuthentication->nodeValue = base64_encode($certificateXDigest);
         $xmlBankPubKeyDigests->appendChild($xmlAuthentication);

         // Add Encryption to BankPubKeyDigests.
         $xmlEncryption = $xml->createElement('Encryption');
         $xmlEncryption->setAttribute('Version', $keyRing->getBankCertificateEVersion());
         $xmlEncryption->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmlenc#sha256');
         $certificateEDigest = $this->cryptService->calculateDigest(
            $keyRing->getBankCertificateE()->toX509()->getPublicKey()->exponent->toHex(),
            $keyRing->getBankCertificateE()->toX509()->getPublicKey()->modulus->toHex()
         );
         $xmlEncryption->nodeValue = base64_encode($certificateEDigest);
         $xmlBankPubKeyDigests->appendChild($xmlEncryption);
      };
   }

   /**
    * Add header and children elements to DOM XML.
    * @param DOMDocument $xml
    * @param DOMElement $xmlRequest
    * @param callable|null $nonce
    * @param callable|null $bank
    * @param callable|null $orderDetails
    * @param callable|null $muttable
    */
   private function handle(DOMDocument $xml, DOMElement $xmlRequest, callable $nonce = null, callable $bank = null, callable $orderDetails = null, callable $muttable = null)
   {
      // Add header to request.
      $xmlHeader = $xml->createElement('header');
      $xmlHeader->setAttribute('authenticate', 'true');
      $xmlRequest->appendChild($xmlHeader);

      // Add static to header.
      $xmlStatic = $xml->createElement('static');
      $xmlHeader->appendChild($xmlStatic);

      // Add HostID to static.
      $xmlHostId = $xml->createElement('HostID');
      $xmlHostId->nodeValue = $this->bank->getHostId();
      $xmlStatic->appendChild($xmlHostId);

      if (null !== $nonce)
      {
         // Add Nonce information to static.
         $nonce($xml, $xmlStatic);
      }

      // Add PartnerID to static.
      $xmlPartnerId = $xml->createElement('PartnerID');
      $xmlPartnerId->nodeValue = $this->user->getPartnerId();
      $xmlStatic->appendChild($xmlPartnerId);

      // Add UserID to static.
      $xmlUserId = $xml->createElement('UserID');
      $xmlUserId->nodeValue = $this->user->getUserId();
      $xmlStatic->appendChild($xmlUserId);

      // Add Product to static.
      $xmlProduct = $xml->createElement('Product');
      $xmlProduct->setAttribute('Language', $this->language);
      $xmlProduct->nodeValue = $this->product;
      $xmlStatic->appendChild($xmlProduct);

      if (null !== $orderDetails)
      {
         // Add OrderDetails information to static.
         $orderDetails($xml, $xmlStatic);
      }

      if (null !== $bank)
      {
         // Add Bank information to static.
         $bank($xml, $xmlStatic);
      }

      // Add SecurityMedium to static.
      $xmlSecurityMedium = $xml->createElement('SecurityMedium');
      $xmlSecurityMedium->nodeValue = $this->securityMedium;
      $xmlStatic->appendChild($xmlSecurityMedium);

      if (null !== $muttable)
      {
         // Add Mutable information to header.
         $muttable($xml, $xmlHeader);
      }
   }

}
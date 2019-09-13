<?php

namespace AndrewSvirin\Ebics\Contracts;

use AndrewSvirin\Ebics\Models\Response;
use DateTime;

/**
 * EBICS client representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
interface EbicsClientInterface
{

   /**
    * Supported protocol version for the Bank.
    * @return Response
    */
   function HEV(): Response;

   /**
    * Make INI request.
    * Send to the bank public certificate of signature A006.
    * Prepare A006 certificates for KeyRing.
    * @param DateTime|null $dateTime Current date.
    * @return Response
    */
   function INI(DateTime $dateTime = null): Response;

   /**
    * Make HIA request.
    * Send to the bank public certificates of authentication (X002) and encryption (E002).
    * Prepare E002 and X002 user certificates for KeyRing.
    * @param DateTime|null $dateTime Current date.
    * @return Response
    */
   function HIA(DateTime $dateTime = null): Response;

   /**
    * Retrieve the Bank public certificates authentication (X002) and encryption (E002).
    * Decrypt OrderData.
    * Prepare E002 and X002 bank certificates for KeyRing.
    * @param DateTime|null $dateTime Current date.
    * @return Response
    */
   function HPB(DateTime $dateTime = null): Response;

   /**
    * Retrieve the bank server parameters.
    * @param DateTime|null $dateTime
    * @return Response
    */
   public function HPD(DateTime $dateTime = null): Response;

   /**
    * Retrieve  Bank available order types.
    * @param DateTime|null $dateTime Current date.
    * @return Response
    */
   function HAA(DateTime $dateTime = null): Response;

   /**
    * Downloads the interim transaction report in SWIFT format (MT942).
    * @param DateTime|null $dateTime Current date.
    * @param DateTime|null $startDateTime The start date of requested transactions.
    * @param DateTime|null $endDateTime The end date of requested transactions.
    * @return Response
    */
   function VMK(DateTime $dateTime = null, DateTime $startDateTime = null, DateTime $endDateTime = null): Response;

   /**
    * Retrieve the bank account statement.
    * @param DateTime|null $dateTime
    * @param DateTime|null $startDateTime The start date of requested transactions.
    * @param DateTime|null $endDateTime The end date of requested transactions.
    * @return Response
    */
   function STA(DateTime $dateTime = null, DateTime $startDateTime = null, DateTime $endDateTime = null): Response;

}
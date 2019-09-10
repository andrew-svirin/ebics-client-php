<?php

namespace AndrewSvirin\Ebics\contracts;

use AndrewSvirin\Ebics\models\Response;
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
    * Make INI request.
    * Send to the bank public certificate of signature A006.
    * Prepare A006 certificates for KeyRing.
    * @param DateTime $dateTime
    * @return Response
    */
   function INI(DateTime $dateTime = null): Response;

   /**
    * Make HIA request.
    * Send to the bank public certificates of authentication (X002) and encryption (E002).
    * Prepare E002 and X002 user certificates for KeyRing.
    * @param DateTime $dateTime
    * @return Response
    */
   function HIA(DateTime $dateTime = null): Response;

   /**
    * Retrieve the Bank public certificates authentication (X002) and encryption (E002).
    * Decrypt OrderData.
    * Prepare E002 and X002 bank certificates for KeyRing.
    * @param DateTime $dateTime
    * @return Response
    */
   function HPB(DateTime $dateTime = null): Response;

   /**
    * Retrieve the dictionary of supported protocol versions.
    * @param DateTime|null $dateTime
    * @return Response
    */
    function HEV(DateTime $dateTime = null): Response;

   /**
    * Retrieve the bank account statement.
    * @param DateTime|null $dateTime
    * @return Response
    */
   public function STA(DateTime $dateTime = null): Response;

}
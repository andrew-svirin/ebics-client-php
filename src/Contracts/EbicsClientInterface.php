<?php

namespace AndrewSvirin\Ebics\Contracts;

use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Models\Response;
use AndrewSvirin\Ebics\Models\User;
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
     */
    public function HEV(Bank $bank): Response;

    /**
     * Make INI request.
     * Send to the bank public certificate of signature A006.
     * Prepare A006 certificates for KeyRing.
     *
     * @param DateTime|null $dateTime current date
     */
    public function INI(Bank $bank, User $user, KeyRing $keyRing, DateTime $dateTime = null): Response;

    /**
     * Make HIA request.
     * Send to the bank public certificates of authentication (X002) and encryption (E002).
     * Prepare E002 and X002 user certificates for KeyRing.
     *
     * @param DateTime|null $dateTime current date
     */
    public function HIA(Bank $bank, User $user, KeyRing $keyRing, DateTime $dateTime = null): Response;

    /**
     * Retrieve the Bank public certificates authentication (X002) and encryption (E002).
     * Decrypt OrderData.
     * Prepare E002 and X002 bank certificates for KeyRing.
     *
     * @param DateTime|null $dateTime current date
     */
    public function HPB(Bank $bank, User $user, KeyRing $keyRing, DateTime $dateTime = null): Response;

    /**
     * Retrieve the bank server parameters.
     */
    public function HPD(Bank $bank, User $user, KeyRing $keyRing,  DateTime $dateTime = null): Response;

    /**
     * Retrieve customer's customer and subscriber information.
     */
    public function HKD(Bank $bank, User $user, KeyRing $keyRing,  DateTime $dateTime = null): Response;

    /**
     * Retrieve subscriber's customer and subscriber information.
     */
    public function HTD(Bank $bank, User $user, KeyRing $keyRing, DateTime $dateTime = null): Response;

    /**
     * Retrieve subscriber's customer and subscriber information.
     */
    public function FDL(Bank $bank, User $user, KeyRing $keyRing,  string $fileInfo, string $format = 'plain', string $countryCode = 'FR', DateTime $dateTime = null, DateTime $startDateTime = null, DateTime $endDateTime = null): Response;

    /**
     * Retrieve  Bank available order types.
     *
     * @param DateTime|null $dateTime current date
     */
    public function HAA(Bank $bank, User $user, KeyRing $keyRing, DateTime $dateTime = null): Response;

    /**
     * Downloads the interim transaction report in SWIFT format (MT942).
     *
     * @param DateTime|null $dateTime      current date
     * @param DateTime|null $startDateTime the start date of requested transactions
     * @param DateTime|null $endDateTime   the end date of requested transactions
     */
    public function VMK(Bank $bank, User $user, KeyRing $keyRing, DateTime $dateTime = null, DateTime $startDateTime = null, DateTime $endDateTime = null): Response;

    /**
     * Retrieve the bank account statement.
     *
     * @param DateTime|null $startDateTime the start date of requested transactions
     * @param DateTime|null $endDateTime   the end date of requested transactions
     */
    public function STA(Bank $bank, User $user, KeyRing $keyRing, DateTime $dateTime = null, DateTime $startDateTime = null, DateTime $endDateTime = null): Response;

    /**
     * Mark transactions as received.
     */
    public function transferReceipt(Bank $bank, KeyRing $keyRing, Response $response, bool $acknowledged = true): Response;
}

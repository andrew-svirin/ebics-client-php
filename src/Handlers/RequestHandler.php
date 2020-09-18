<?php

namespace AndrewSvirin\Ebics\Handlers;

use AndrewSvirin\Ebics\Contracts\TransactionInterface;
use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\Certificate;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Models\OrderData;
use AndrewSvirin\Ebics\Models\Request;
use AndrewSvirin\Ebics\Models\Transaction;
use AndrewSvirin\Ebics\Models\User;
use DateTime;

/**
 * Class RequestFactory represents producers for the @see Request.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class RequestHandler
{
    /**
     * @var EbicsRequestHandler
     */
    private $ebicsRequestHandler;
    /**
     * @var HeaderHandler
     */
    private $headerHandler;
    /**
     * @var BodyHandler
     */
    private $bodyHandler;
    /**
     * @var OrderDataHandler
     */
    private $orderDataHandler;
    /**
     * @var AuthSignatureHandler
     */
    private $authSignatureHandler;
    /**
     * @var HostHandler
     */
    private $hostHandler;

    public function __construct(
        EbicsRequestHandler $ebicsRequestHandler = null,
        HeaderHandler $headerHandler = null,
        BodyHandler $bodyHandler = null,
        OrderDataHandler $orderDataHandler = null,
        AuthSignatureHandler $authSignatureHandler = null,
        HostHandler $hostHandler = null
    ) {
        $this->ebicsRequestHandler = $ebicsRequestHandler ?? new EbicsRequestHandler();
        $this->headerHandler = $headerHandler ?? new HeaderHandler();
        $this->bodyHandler = $bodyHandler ?? new BodyHandler();
        $this->orderDataHandler = $orderDataHandler ??  new OrderDataHandler();
        $this->authSignatureHandler = $authSignatureHandler ??  new AuthSignatureHandler();
        $this->hostHandler = $hostHandler ??  new HostHandler();
    }

    public function buildINI(Bank $bank, User $user, KeyRing $keyRing, Certificate $certificateA, DateTime $dateTime): Request
    {
        // Order data.
        $orderDataContent = $this->orderDataHandler->handleINI($bank, $user, $keyRing, new OrderData(), $certificateA, $dateTime)->getContent();
        // Wrapper for request Order data.
        $request = new Request();
        $xmlRequest = $this->ebicsRequestHandler->handleUnsecured($request);
        $this->headerHandler->handleINI($bank, $user, $request, $xmlRequest);

        return $this->bodyHandler->handle($request, $xmlRequest, $orderDataContent);
    }

    public function buildHEV(Bank $bank): Request
    {
        $request = new Request();
        $xmlRequest = $this->ebicsRequestHandler->handleHEV($request);

        return $this->hostHandler->handle($bank, $request, $xmlRequest);
    }

    public function buildHIA(Bank $bank, User $user, KeyRing $keyRing, Certificate $certificateE, Certificate $certificateX, DateTime $dateTime): Request
    {
        // Order data.
        $orderData = $this->orderDataHandler->handleHIA($bank, $user, $keyRing, new OrderData(), $certificateE, $certificateX, $dateTime);
        $orderDataContent = $orderData->getContent();
        // Wrapper for request Order data.
        $request = new Request();
        $xmlRequest = $this->ebicsRequestHandler->handleUnsecured($request);
        $this->headerHandler->handleHIA($bank, $user, $request, $xmlRequest);

        return $this->bodyHandler->handle($request, $xmlRequest, $orderDataContent);
    }

    /**
     * @throws EbicsException
     */
    public function buildHPB(Bank $bank, User $user, KeyRing $keyRing, DateTime $dateTime): Request
    {
        $request = new Request();
        $xmlRequest = $this->ebicsRequestHandler->handleNoPubKeyDigests($request);
        $this->headerHandler->handleHPB($bank, $user, $request, $xmlRequest, $dateTime);
        $this->authSignatureHandler->handle($keyRing, $request, $xmlRequest);

        return $this->bodyHandler->handleEmpty($request, $xmlRequest);
    }

    /**
     * @throws EbicsException
     */
    public function buildHPD(Bank $bank, User $user, KeyRing $keyRing, DateTime $dateTime): Request
    {
        $request = new Request();
        $xmlRequest = $this->ebicsRequestHandler->handleSecured($request);
        $this->headerHandler->handleHPD($bank, $user, $keyRing,$request, $xmlRequest, $dateTime);
        $this->authSignatureHandler->handle($keyRing, $request, $xmlRequest);

        return $this->bodyHandler->handleEmpty($request, $xmlRequest);
    }

    /**
     * @throws EbicsException
     */
    public function buildHKD(Bank $bank, User $user, KeyRing $keyRing, DateTime $dateTime): Request
    {
        $request = new Request();
        $xmlRequest = $this->ebicsRequestHandler->handleSecured($request);
        $this->headerHandler->handleHKD($bank, $user, $keyRing,$request, $xmlRequest, $dateTime);
        $this->authSignatureHandler->handle($keyRing, $request, $xmlRequest);

        return $this->bodyHandler->handleEmpty($request, $xmlRequest);
    }

    /**
     * @throws EbicsException
     */
    public function buildHTD(Bank $bank, User $user, KeyRing $keyRing, DateTime $dateTime): Request
    {
        $request = new Request();
        $xmlRequest = $this->ebicsRequestHandler->handleSecured($request);
        $this->headerHandler->handleHTD($bank, $user, $keyRing, $request, $xmlRequest, $dateTime);
        $this->authSignatureHandler->handle($keyRing, $request, $xmlRequest);

        return $this->bodyHandler->handleEmpty($request, $xmlRequest);
    }

    /**
     * @throws EbicsException
     */
    public function buildFDL(Bank $bank, User $user, KeyRing $keyRing, DateTime $dateTime, string $fileInfo, string $countryCode, DateTime $startDateTime = null, DateTime $endDateTime = null): Request
    {
        $request = new Request();
        $xmlRequest = $this->ebicsRequestHandler->handleSecured($request);
        $this->headerHandler->handleFDL($bank, $user, $keyRing, $request, $xmlRequest, $dateTime, $fileInfo, $countryCode, $startDateTime, $endDateTime);
        $this->authSignatureHandler->handle($keyRing, $request, $xmlRequest);

        return $this->bodyHandler->handleEmpty($request, $xmlRequest);
    }

    /**
     * @throws EbicsException
     */
    public function buildHAA(Bank $bank, User $user, KeyRing $keyRing, DateTime $dateTime): Request
    {
        $request = new Request();
        $xmlRequest = $this->ebicsRequestHandler->handleSecured($request);
        $this->headerHandler->handleHAA($bank, $user, $keyRing, $request, $xmlRequest, $dateTime);
        $this->authSignatureHandler->handle($keyRing, $request, $xmlRequest);

        return $this->bodyHandler->handleEmpty($request, $xmlRequest);
    }

    /**
     * @throws EbicsException
     */
    public function buildTransferReceipt(Bank $bank, KeyRing $keyRing, Transaction $transaction, bool $acknowledged): Request
    {
        $request = new Request();
        $xmlRequest = $this->ebicsRequestHandler->handleSecured($request);
        $this->headerHandler->handleTransferReceipt($bank, $request, $xmlRequest, $transaction);
        $this->bodyHandler->handleTransferReceipt($request, $xmlRequest, true === $acknowledged ? TransactionInterface::CODE_RECEIPT_POSITIVE : TransactionInterface::CODE_RECEIPT_NEGATIVE);
        $this->authSignatureHandler->handle($keyRing, $request, $xmlRequest);

        return $request;
    }

    /**
     * @throws EbicsException
     */
    public function buildVMK(Bank $bank, User $user, KeyRing $keyRing, DateTime $dateTime, DateTime $startDateTime = null, DateTime $endDateTime = null): Request
    {
        $request = new Request();
        $xmlRequest = $this->ebicsRequestHandler->handleSecured($request);
        $this->headerHandler->handleVMK($bank, $user, $keyRing, $request, $xmlRequest, $dateTime, $startDateTime, $endDateTime);
        $this->authSignatureHandler->handle($keyRing, $request, $xmlRequest);

        return $this->bodyHandler->handleEmpty($request, $xmlRequest);
    }

    /**
     * @throws EbicsException
     */
    public function buildSTA(Bank $bank, User $user, KeyRing $keyRing, DateTime $dateTime, DateTime $startDateTime = null, DateTime $endDateTime = null): Request
    {
        $request = new Request();
        $xmlRequest = $this->ebicsRequestHandler->handleSecured($request);
        $this->headerHandler->handleSTA($bank, $user, $keyRing, $request, $xmlRequest, $dateTime, $startDateTime, $endDateTime);
        $this->authSignatureHandler->handle($keyRing, $request, $xmlRequest);

        return $this->bodyHandler->handleEmpty($request, $xmlRequest);
    }
}

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

    /**
     * Constructor.
     */
    public function __construct(Bank $bank, User $user, KeyRing $keyRing)
    {
        $this->ebicsRequestHandler = new EbicsRequestHandler();
        $this->headerHandler = new HeaderHandler($bank, $user, $keyRing);
        $this->bodyHandler = new BodyHandler();
        $this->orderDataHandler = new OrderDataHandler($bank, $user, $keyRing);
        $this->authSignatureHandler = new AuthSignatureHandler($keyRing);
        $this->hostHandler = new HostHandler($bank);
    }

    public function buildINI(Certificate $certificateA, DateTime $dateTime): Request
    {
        // Order data.
        $orderData = new OrderData();
        $this->orderDataHandler->handleINI($orderData, $certificateA, $dateTime);
        $orderDataContent = $orderData->getContent();
        // Wrapper for request Order data.
        $request = new Request();
        $xmlRequest = $this->ebicsRequestHandler->handleUnsecured($request);
        $this->headerHandler->handleINI($request, $xmlRequest);
        $this->bodyHandler->handle($request, $xmlRequest, $orderDataContent);

        return $request;
    }

    public function buildHEV(): Request
    {
        $request = new Request();
        $xmlRequest = $this->ebicsRequestHandler->handleHEV($request);
        $this->hostHandler->handle($request, $xmlRequest);

        return $request;
    }

    public function buildHIA(Certificate $certificateE, Certificate $certificateX, DateTime $dateTime): Request
    {
        // Order data.
        $orderData = new OrderData();
        $this->orderDataHandler->handleHIA($orderData, $certificateE, $certificateX, $dateTime);
        $orderDataContent = $orderData->getContent();
        // Wrapper for request Order data.
        $request = new Request();
        $xmlRequest = $this->ebicsRequestHandler->handleUnsecured($request);
        $this->headerHandler->handleHIA($request, $xmlRequest);
        $this->bodyHandler->handle($request, $xmlRequest, $orderDataContent);

        return $request;
    }

    /**
     * @throws EbicsException
     */
    public function buildHPB(DateTime $dateTime): Request
    {
        $request = new Request();
        $xmlRequest = $this->ebicsRequestHandler->handleNoPubKeyDigests($request);
        $this->headerHandler->handleHPB($request, $xmlRequest, $dateTime);
        $this->authSignatureHandler->handle($request, $xmlRequest);
        $this->bodyHandler->handleEmpty($request, $xmlRequest);

        return $request;
    }

    /**
     * @throws EbicsException
     */
    public function buildHPD(DateTime $dateTime): Request
    {
        $request = new Request();
        $xmlRequest = $this->ebicsRequestHandler->handleSecured($request);
        $this->headerHandler->handleHPD($request, $xmlRequest, $dateTime);
        $this->authSignatureHandler->handle($request, $xmlRequest);
        $this->bodyHandler->handleEmpty($request, $xmlRequest);

        return $request;
    }

    /**
     * @throws EbicsException
     */
    public function buildHKD(DateTime $dateTime): Request
    {
        $request = new Request();
        $xmlRequest = $this->ebicsRequestHandler->handleSecured($request);
        $this->headerHandler->handleHKD($request, $xmlRequest, $dateTime);
        $this->authSignatureHandler->handle($request, $xmlRequest);
        $this->bodyHandler->handleEmpty($request, $xmlRequest);

        return $request;
    }

    /**
     * @throws EbicsException
     */
    public function buildHTD(DateTime $dateTime): Request
    {
        $request = new Request();
        $xmlRequest = $this->ebicsRequestHandler->handleSecured($request);
        $this->headerHandler->handleHTD($request, $xmlRequest, $dateTime);
        $this->authSignatureHandler->handle($request, $xmlRequest);
        $this->bodyHandler->handleEmpty($request, $xmlRequest);

        return $request;
    }

    /**
     * @throws EbicsException
     */
    public function buildFDL(DateTime $dateTime, string $fileInfo, string $countryCode, DateTime $startDateTime = null, DateTime $endDateTime = null): Request
    {
        $request = new Request();
        $xmlRequest = $this->ebicsRequestHandler->handleSecured($request);
        $this->headerHandler->handleFDL($request, $xmlRequest, $dateTime, $fileInfo, $countryCode, $startDateTime, $endDateTime);
        $this->authSignatureHandler->handle($request, $xmlRequest);
        $this->bodyHandler->handleEmpty($request, $xmlRequest);

        return $request;
    }

    /**
     * @throws EbicsException
     */
    public function buildHAA(DateTime $dateTime): Request
    {
        $request = new Request();
        $xmlRequest = $this->ebicsRequestHandler->handleSecured($request);
        $this->headerHandler->handleHAA($request, $xmlRequest, $dateTime);
        $this->authSignatureHandler->handle($request, $xmlRequest);
        $this->bodyHandler->handleEmpty($request, $xmlRequest);

        return $request;
    }

    /**
     * @throws EbicsException
     */
    public function buildTransferReceipt(Transaction $transaction, bool $acknowledged): Request
    {
        $request = new Request();
        $xmlRequest = $this->ebicsRequestHandler->handleSecured($request);
        $this->headerHandler->handleTransferReceipt($request, $xmlRequest, $transaction);
        $this->bodyHandler->handleTransferReceipt($request, $xmlRequest, true === $acknowledged ? TransactionInterface::CODE_RECEIPT_POSITIVE : TransactionInterface::CODE_RECEIPT_NEGATIVE);
        $this->authSignatureHandler->handle($request, $xmlRequest);

        return $request;
    }

    /**
     * @throws EbicsException
     */
    public function buildVMK(DateTime $dateTime, DateTime $startDateTime = null, DateTime $endDateTime = null): Request
    {
        $request = new Request();
        $xmlRequest = $this->ebicsRequestHandler->handleSecured($request);
        $this->headerHandler->handleVMK($request, $xmlRequest, $dateTime, $startDateTime, $endDateTime);
        $this->authSignatureHandler->handle($request, $xmlRequest);
        $this->bodyHandler->handleEmpty($request, $xmlRequest);

        return $request;
    }

    /**
     * @throws EbicsException
     */
    public function buildSTA(DateTime $dateTime, DateTime $startDateTime = null, DateTime $endDateTime = null): Request
    {
        $request = new Request();
        $xmlRequest = $this->ebicsRequestHandler->handleSecured($request);
        $this->headerHandler->handleSTA($request, $xmlRequest, $dateTime, $startDateTime, $endDateTime);
        $this->authSignatureHandler->handle($request, $xmlRequest);
        $this->bodyHandler->handleEmpty($request, $xmlRequest);

        return $request;
    }
}

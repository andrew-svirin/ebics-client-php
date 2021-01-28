<?php

namespace AndrewSvirin\Ebics\Contexts;

use AndrewSvirin\Ebics\Contracts\SignatureInterface;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Models\User;
use DateTimeInterface;

/**
 * Class RequestContext context container for @see \AndrewSvirin\Ebics\Factories\RequestFactory
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class RequestContext
{

    /**
     * @var Bank
     */
    private $bank;

    /**
     * @var User
     */
    private $user;

    /**
     * @var KeyRing
     */
    private $keyRing;

    /**
     * @var SignatureInterface
     */
    private $certificateA;

    /**
     * @var SignatureInterface
     */
    private $certificateE;

    /**
     * @var SignatureInterface
     */
    private $certificateX;
    /**
     * @var DateTimeInterface
     */
    private $dateTime;

    /**
     * @var DateTimeInterface|null
     */
    private $startDateTime;

    /**
     * @var DateTimeInterface|null
     */
    private $endDateTime;

    /**
     * @var string
     */
    private $fileFormat;

    /**
     * @var string
     */
    private $countryCode;

    /**
     * @var string
     */
    private $receiptCode;

    /**
     * @var string
     */
    private $transactionId;

    public function setBank(Bank $bank): RequestContext
    {
        $this->bank = $bank;

        return $this;
    }

    public function getBank(): Bank
    {
        return $this->bank;
    }

    public function setUser(User $user): RequestContext
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setKeyRing(KeyRing $keyRing): RequestContext
    {
        $this->keyRing = $keyRing;

        return $this;
    }

    public function getKeyRing(): KeyRing
    {
        return $this->keyRing;
    }

    public function setCertificateA(SignatureInterface $certificateA): RequestContext
    {
        $this->certificateA = $certificateA;

        return $this;
    }

    public function getCertificateA(): SignatureInterface
    {
        return $this->certificateA;
    }

    public function setCertificateE(SignatureInterface $certificateE): RequestContext
    {
        $this->certificateE = $certificateE;

        return $this;
    }

    public function getCertificateE(): SignatureInterface
    {
        return $this->certificateE;
    }

    public function setCertificateX(SignatureInterface $certificateX): RequestContext
    {
        $this->certificateX = $certificateX;

        return $this;
    }

    public function getCertificateX(): SignatureInterface
    {
        return $this->certificateX;
    }

    public function setDateTime(DateTimeInterface $dateTime): RequestContext
    {
        $this->dateTime = $dateTime;

        return $this;
    }

    public function getDateTime(): DateTimeInterface
    {
        return $this->dateTime;
    }

    public function setStartDateTime(?DateTimeInterface $startDateTime): RequestContext
    {
        $this->startDateTime = $startDateTime;

        return $this;
    }

    public function getStartDateTime(): ?DateTimeInterface
    {
        return $this->startDateTime;
    }

    public function setEndDateTime(?DateTimeInterface $endDateTime): RequestContext
    {
        $this->endDateTime = $endDateTime;

        return $this;
    }

    public function getEndDateTime(): ?DateTimeInterface
    {
        return $this->endDateTime;
    }

    public function setFileFormat(string $fileFormat): RequestContext
    {
        $this->fileFormat = $fileFormat;

        return $this;
    }

    public function getFileFormat(): string
    {
        return $this->fileFormat;
    }

    public function setCountryCode(string $countryCode): RequestContext
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function setReceiptCode(string $receiptCode): RequestContext
    {
        $this->receiptCode = $receiptCode;

        return $this;
    }

    public function getReceiptCode(): string
    {
        return $this->receiptCode;
    }

    public function setTransactionId(string $transactionId): RequestContext
    {
        $this->transactionId = $transactionId;

        return $this;
    }

    public function getTransactionId(): string
    {
        return $this->transactionId;
    }
}

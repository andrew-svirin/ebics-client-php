<?php

namespace AndrewSvirin\Ebics\Contexts;

use AndrewSvirin\Ebics\Contracts\SignatureDataInterface;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\Keyring;
use AndrewSvirin\Ebics\Models\User;
use DateTimeInterface;

/**
 * Class RequestContext context container for @see \AndrewSvirin\Ebics\Factories\RequestFactory
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class RequestContext
{
    private Bank $bank;
    private User $user;
    private Keyring $keyring;
    private DateTimeInterface $dateTime;
    private ?DateTimeInterface $startDateTime;
    private ?DateTimeInterface $endDateTime;
    private string $fileFormat;
    private string $countryCode;
    private string $receiptCode;
    private ?int $segmentNumber;
    private ?bool $isLastSegment;
    private string $transactionId;
    private string $transactionKey;
    private int $numSegments;
    private string $orderData;
    private SignatureDataInterface $signatureData;
    private string $dataDigest;
    private string $signatureVersion;
    private BTDContext $btdContext;
    private BTUContext $btuContext;
    private HVEContext $hveContext;
    private HVDContext $hvdContext;
    private HVTContext $hvtContext;
    private FULContext $fulContext;

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

    public function setKeyring(Keyring $keyring): RequestContext
    {
        $this->keyring = $keyring;

        return $this;
    }

    public function getKeyring(): Keyring
    {
        return $this->keyring;
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

    public function setSegmentNumber(?int $segmentNumber): RequestContext
    {
        $this->segmentNumber = $segmentNumber;

        return $this;
    }

    public function getSegmentNumber(): ?int
    {
        return $this->segmentNumber;
    }

    public function setIsLastSegment(?bool $isLastSegment): RequestContext
    {
        $this->isLastSegment = $isLastSegment;

        return $this;
    }

    public function getIsLastSegment(): ?bool
    {
        return $this->isLastSegment;
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

    public function setTransactionKey(string $transactionKey): RequestContext
    {
        $this->transactionKey = $transactionKey;

        return $this;
    }

    public function getTransactionKey(): string
    {
        return $this->transactionKey;
    }

    public function setNumSegments(int $numSegments): RequestContext
    {
        $this->numSegments = $numSegments;

        return $this;
    }

    public function getNumSegments(): int
    {
        return $this->numSegments;
    }

    public function setOrderData(string $orderData): RequestContext
    {
        $this->orderData = $orderData;

        return $this;
    }

    public function getOrderData(): string
    {
        return $this->orderData;
    }

    public function setSignatureData(SignatureDataInterface $signatureData): RequestContext
    {
        $this->signatureData = $signatureData;

        return $this;
    }

    public function getSignatureData(): SignatureDataInterface
    {
        return $this->signatureData;
    }

    public function setBTDContext(BTDContext $btdContext): RequestContext
    {
        $this->btdContext = $btdContext;

        return $this;
    }

    public function getBTDContext(): BTDContext
    {
        return $this->btdContext;
    }

    public function setHVEContext(HVEContext $hveContext): RequestContext
    {
        $this->hveContext = $hveContext;

        return $this;
    }

    public function getHVEContext(): HVEContext
    {
        return $this->hveContext;
    }

    public function setHVDContext(HVDContext $hvdContext): RequestContext
    {
        $this->hvdContext = $hvdContext;

        return $this;
    }

    public function getHVDContext(): HVDContext
    {
        return $this->hvdContext;
    }

    public function setHVTContext(HVTContext $hvtContext): RequestContext
    {
        $this->hvtContext = $hvtContext;

        return $this;
    }

    public function getHVTContext(): HVTContext
    {
        return $this->hvtContext;
    }

    public function setFULContext(FULContext $fulContext): RequestContext
    {
        $this->fulContext = $fulContext;

        return $this;
    }

    public function getFULContext(): FULContext
    {
        return $this->fulContext;
    }

    public function setBTUContext(BTUContext $btuContext): RequestContext
    {
        $this->btuContext = $btuContext;

        return $this;
    }

    public function getBTUContext(): BTUContext
    {
        return $this->btuContext;
    }

    public function setDataDigest(?string $dataDigest): RequestContext
    {
        $this->dataDigest = $dataDigest;

        return $this;
    }

    public function getDataDigest(): ?string
    {
        return $this->dataDigest;
    }

    public function setSignatureVersion(string $signatureVersion): RequestContext
    {
        $this->signatureVersion = $signatureVersion;

        return $this;
    }

    public function getSignatureVersion(): string
    {
        return $this->signatureVersion;
    }
}

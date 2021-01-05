<?php

namespace AndrewSvirin\Ebics\Models;

use DateTime;

/**
 * Class SignatureBankLetter represents SignatureBankLetter model.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class SignatureBankLetter
{

    const TYPE_A = 'A';
    const TYPE_X = 'X';
    const TYPE_E = 'E';

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $exponent;

    /**
     * @var string
     */
    private $modulus;

    /**
     * @var string
     */
    private $keyHash;

    /**
     * @var string|null
     */
    private $certificateContent;

    /**
     * @var DateTime|null
     */
    private $certificateCreatedAt;

    /**
     * @var bool
     */
    private $isCertified;

    public function __construct(
        string $type,
        string $version,
        string $exponent,
        string $modulus,
        string $keyHash
    ) {
        $this->type = $type;
        $this->version = $version;
        $this->exponent = $exponent;
        $this->modulus = $modulus;
        $this->keyHash = $keyHash;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getExponent(): string
    {
        return $this->exponent;
    }

    /**
     * @return string
     */
    public function getModulus(): string
    {
        return $this->modulus;
    }

    /**
     * @return string
     */
    public function getKeyHash(): string
    {
        return $this->keyHash;
    }

    /**
     * @param string|null $certificateContent
     */
    public function setCertificateContent(?string $certificateContent): void
    {
        $this->certificateContent = $certificateContent;
    }

    /**
     * @return string|null
     */
    public function getCertificateContent(): ?string
    {
        return $this->certificateContent;
    }

    /**
     * @param DateTime|null $certificateCreatedAt
     */
    public function setCertificateCreatedAt(?DateTime $certificateCreatedAt): void
    {
        $this->certificateCreatedAt = $certificateCreatedAt;
    }

    /**
     * @return DateTime|null
     */
    public function getCertificateCreatedAt(): ?DateTime
    {
        return $this->certificateCreatedAt;
    }

    /**
     * @param bool $isCertified
     */
    public function setIsCertified(bool $isCertified): void
    {
        $this->isCertified = $isCertified;
    }

    /**
     * @return bool
     */
    public function isCertified(): bool
    {
        return (bool)$this->isCertified;
    }
}

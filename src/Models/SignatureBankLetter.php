<?php

namespace EbicsApi\Ebics\Models;

use DateTime;

/**
 * Class SignatureBankLetter represents SignatureBankLetter model.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class SignatureBankLetter
{
    const TYPE_A = 'A';
    const TYPE_X = 'X';
    const TYPE_E = 'E';

    private string $type;
    private string $version;
    private string $exponent;
    private string $modulus;
    private string $keyHash;
    private int $modulusSize;
    private ?string $certificateContent = null;
    private ?DateTime $certificateCreatedAt;

    public function __construct(
        string $type,
        string $version,
        string $exponent,
        string $modulus,
        string $keyHash,
        int $modulusSize
    ) {
        $this->type = $type;
        $this->version = $version;
        $this->exponent = $exponent;
        $this->modulus = $modulus;
        $this->keyHash = $keyHash;
        $this->modulusSize = $modulusSize;
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
     * @return int
     */
    public function getModulusSize(): int
    {
        return $this->modulusSize;
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
        return $this->certificateContent ?? null;
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
        return $this->certificateCreatedAt ?? null;
    }

    /**
     * @return bool
     */
    public function isCertified(): bool
    {
        return null !== $this->certificateContent;
    }
}

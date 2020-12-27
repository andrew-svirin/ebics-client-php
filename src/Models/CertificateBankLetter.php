<?php

namespace AndrewSvirin\Ebics\Models;

/**
 * Class Certificate represents Certificate model.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class CertificateBankLetter
{

    /**
     * @var string
     */
    private $certificateVersion;

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

    public function __construct(string $certificateVersion, string $exponent, string $modulus, string $keyHash)
    {
        $this->certificateVersion = $certificateVersion;
        $this->exponent = $exponent;
        $this->modulus = $modulus;
        $this->keyHash = $keyHash;
    }

    /**
     * @return string
     */
    public function getCertificateVersion(): string
    {
        return $this->certificateVersion;
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
}

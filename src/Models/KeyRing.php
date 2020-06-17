<?php

namespace AndrewSvirin\Ebics\Models;

use AndrewSvirin\Ebics\Exceptions\EbicsException;

/**
 * EBICS key ring representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class KeyRing
{
    /**
     * @var Certificate|null
     */
    private $userCertificateA;

    /**
     * @var Certificate|null
     */
    private $userCertificateX;

    /**
     * @var Certificate|null
     */
    private $userCertificateE;

    /**
     * @var Certificate|null
     */
    private $bankCertificateX;

    /**
     * @var Certificate|null
     */
    private $bankCertificateE;

    /**
     * @var string|null
     */
    private $password;

    public function setUserCertificateA(Certificate $certificate) : void
    {
        $this->userCertificateA = $certificate;
    }

    public function getUserCertificateA(): ?Certificate
    {
        return $this->userCertificateA;
    }

    public function getUserCertificateAVersion(): string
    {
        return 'A006';
    }

    public function setUserCertificateX(Certificate $certificate) : void
    {
        $this->userCertificateX = $certificate;
    }

    public function getUserCertificateX(): ?Certificate
    {
        return $this->userCertificateX;
    }

    public function getUserCertificateXVersion(): string
    {
        return 'X002';
    }

    public function setUserCertificateE(Certificate $certificate) : void
    {
        $this->userCertificateE = $certificate;
    }

    public function getUserCertificateE(): ?Certificate
    {
        return $this->userCertificateE;
    }

    public function getUserCertificateEVersion(): string
    {
        return 'E002';
    }

    public function setPassword(string $password) : void
    {
        $this->password = $password;
    }

    public function getPassword(): string
    {
        if ($this->password === null) {
            throw new EbicsException('Password must be set');
        }

        return $this->password;
    }

    public function setBankCertificateX(Certificate $bankCertificateX): void
    {
        $this->bankCertificateX = $bankCertificateX;
    }

    public function getBankCertificateX(): ?Certificate
    {
        return $this->bankCertificateX;
    }

    public function getBankCertificateXVersion(): string
    {
        return 'X002';
    }

    public function setBankCertificateE(Certificate $bankCertificateE): void
    {
        $this->bankCertificateE = $bankCertificateE;
    }

    public function getBankCertificateE(): ?Certificate
    {
        return $this->bankCertificateE;
    }

    public function getBankCertificateEVersion(): string
    {
        return 'E002';
    }
}

<?php

namespace AndrewSvirin\Ebics\Models;

use AndrewSvirin\Ebics\Contracts\SignatureInterface;
use AndrewSvirin\Ebics\Exceptions\PasswordEbicsException;

/**
 * EBICS key ring representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class KeyRing
{
    const USER_PREFIX = 'USER';
    const BANK_PREFIX = 'BANK';
    const SIGNATURE_PREFIX_A = 'A';
    const SIGNATURE_PREFIX_X = 'X';
    const SIGNATURE_PREFIX_E = 'E';
    const CERTIFICATE_PREFIX = 'CERTIFICATE';
    const PUBLIC_KEY_PREFIX = 'PUBLIC_KEY';
    const PRIVATE_KEY_PREFIX = 'PRIVATE_KEY';

    private ?SignatureInterface $userSignatureA = null;
    private ?SignatureInterface $userSignatureX = null;
    private ?SignatureInterface $userSignatureE = null;
    private ?SignatureInterface $bankSignatureX = null;
    private ?SignatureInterface $bankSignatureE = null;
    private ?string $password = null;

    public function setUserSignatureA(SignatureInterface $signature = null): void
    {
        $this->userSignatureA = $signature;
    }

    /**
     * @return SignatureInterface|null
     */
    public function getUserSignatureA(): ?SignatureInterface
    {
        return $this->userSignatureA;
    }

    /**
     * @return string
     */
    public function getUserSignatureAVersion(): string
    {
        return 'A005';
    }

    public function setUserSignatureX(SignatureInterface $signature = null): void
    {
        $this->userSignatureX = $signature;
    }

    /**
     * @return SignatureInterface|null
     */
    public function getUserSignatureX(): ?SignatureInterface
    {
        return $this->userSignatureX;
    }

    /**
     * @return string
     */
    public function getUserSignatureXVersion(): string
    {
        return 'X002';
    }

    public function setUserSignatureE(SignatureInterface $signature = null): void
    {
        $this->userSignatureE = $signature;
    }

    /**
     * @return SignatureInterface|null
     */
    public function getUserSignatureE(): ?SignatureInterface
    {
        return $this->userSignatureE;
    }

    /**
     * @return string
     */
    public function getUserSignatureEVersion(): string
    {
        return 'E002';
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return string
     * @throws PasswordEbicsException
     */
    public function getPassword(): string
    {
        if ($this->password === null) {
            throw new PasswordEbicsException('Password must be set');
        }

        return $this->password;
    }

    public function setBankSignatureX(SignatureInterface $bankSignatureX = null): void
    {
        $this->bankSignatureX = $bankSignatureX;
    }

    /**
     * @return SignatureInterface|null
     */
    public function getBankSignatureX(): ?SignatureInterface
    {
        return $this->bankSignatureX;
    }

    /**
     * @return string
     */
    public function getBankSignatureXVersion(): string
    {
        return 'X002';
    }

    public function setBankSignatureE(SignatureInterface $bankSignatureE = null): void
    {
        $this->bankSignatureE = $bankSignatureE;
    }

    /**
     * @return SignatureInterface|null
     */
    public function getBankSignatureE(): ?SignatureInterface
    {
        return $this->bankSignatureE;
    }

    /**
     * @return string
     */
    public function getBankSignatureEVersion(): string
    {
        return 'E002';
    }
}

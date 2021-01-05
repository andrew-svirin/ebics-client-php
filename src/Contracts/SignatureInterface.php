<?php

namespace AndrewSvirin\Ebics\Contracts;

/**
 * EBICS SignatureInterface representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
interface SignatureInterface
{
    const TYPE_A = 'A';
    const TYPE_X = 'X';
    const TYPE_E = 'E';

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @return string
     */
    public function getPublicKey(): string;

    /**
     * @return string|null
     */
    public function getPrivateKey(): ?string;

    /**
     * @param string|null $certificateContent
     */
    public function setCertificateContent(?string $certificateContent): void;

    /**
     * @return string|null
     */
    public function getCertificateContent();
}

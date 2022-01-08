<?php

namespace AndrewSvirin\Ebics\Models;

use AndrewSvirin\Ebics\Contracts\SignatureInterface;

/**
 * Class Signature represents Signature model.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class Signature implements SignatureInterface
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $publicKey;

    /**
     * Private key null for represent bank signature.
     * @var string|null
     */
    private $privateKey;

    /**
     * @var string|null
     */
    private $certificateContent;

    /**
     * @param string $type
     * @param string $publicKey
     * @param string|null $privateKey
     */
    public function __construct(string $type, string $publicKey, string $privateKey = null)
    {
        $this->type = $type;
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * @inheritDoc
     */
    public function getPrivateKey(): ?string
    {
        return $this->privateKey;
    }

    /**
     * @inheritDoc
     */
    public function setCertificateContent(?string $certificateContent): void
    {
        $this->certificateContent = $certificateContent;
    }

    /**
     * @inheritDoc
     */
    public function getCertificateContent(): ?string
    {
        return $this->certificateContent;
    }
}

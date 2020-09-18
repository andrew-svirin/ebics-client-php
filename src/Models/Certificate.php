<?php

namespace AndrewSvirin\Ebics\Models;

use AndrewSvirin\Ebics\Contracts\CertificateInterface;

/**
 * Class Certificate represents Certificate model.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class Certificate implements CertificateInterface
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
     * @var string|null
     */
    private $privateKey;

    /**
     * @var string|null
     */
    private $content;

    public function __construct(string $type, string $publicKey, string $privateKey = null, string $content = null)
    {
        $this->type = $type;
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
        $this->content = $content;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function getPrivateKey(): ?string
    {
        return $this->privateKey;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Represents Certificate in the structure X509.
     */
    public function toX509(): ?CertificateX509
    {
        if (null === $this->content) {
            return null;
        }
        $x509 = new CertificateX509();
        $x509->loadX509($this->content);

        return $x509;
    }
}

<?php

namespace AndrewSvirin\Ebics\Models;

use AndrewSvirin\Ebics\Contracts\CertificateInterface;
use AndrewSvirin\Ebics\Exceptions\EbicsException;
use function Safe\sprintf;

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
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * @return string|null
     */
    public function getPrivateKey(): ?string
    {
        return $this->privateKey;
    }

    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getContentOrThrowException(): string
    {
        if ($this->content === null) {
            throw new EbicsException(sprintf('Certificat content is empty for type "%s"', $this->type));
        }

        return $this->content;
    }

    /**
     * Represents Certificate in the structure X509.
     *
     * @return CertificateX509|null
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

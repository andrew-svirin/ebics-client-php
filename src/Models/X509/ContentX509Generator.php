<?php

namespace EbicsApi\Ebics\Models\X509;

use EbicsApi\Ebics\Contracts\Crypt\RSAInterface;
use EbicsApi\Ebics\Contracts\Crypt\X509Interface;
use EbicsApi\Ebics\Contracts\X509GeneratorInterface;
use EbicsApi\Ebics\Models\Crypt\X509;

/**
 * Generator simulation for already created certificates and loaded from content.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class ContentX509Generator implements X509GeneratorInterface
{
    private string $aContent;

    private string $eContent;

    private string $xContent;

    public function setAContent(string $content): void
    {
        $this->aContent = $content;
    }

    public function setEContent(string $content): void
    {
        $this->eContent = $content;
    }

    public function setXContent(string $content): void
    {
        $this->xContent = $content;
    }

    public function generateAX509(RSAInterface $privateKey, RSAInterface $publicKey): X509Interface
    {
        $cert = new X509();

        $cert->loadX509($this->aContent);

        return $cert;
    }

    public function generateEX509(RSAInterface $privateKey, RSAInterface $publicKey): X509Interface
    {
        $cert = new X509();

        $cert->loadX509($this->eContent);

        return $cert;
    }

    public function generateXX509(RSAInterface $privateKey, RSAInterface $publicKey): X509Interface
    {
        $cert = new X509();

        $cert->loadX509($this->xContent);

        return $cert;
    }
}

<?php

namespace AndrewSvirin\Ebics\Factories;

use AndrewSvirin\Ebics\Factories\Crypt\BigIntegerFactory;
use AndrewSvirin\Ebics\Factories\Crypt\RSAFactory;
use AndrewSvirin\Ebics\Factories\X509\X509GeneratorFactory;
use AndrewSvirin\Ebics\Models\Certificate;
use AndrewSvirin\Ebics\Models\Crypt\RSA;

/**
 * Class CertificateFactory represents producers for the @see Certificate.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin, Guillaume Sainthillier
 */
class CertificateFactory
{

    /**
     * @var RSAFactory
     */
    private $rsaFactory;

    /**
     * @var BigIntegerFactory
     */
    private $bigIntegerFactory;

    public function __construct()
    {
        $this->rsaFactory = new RSAFactory();
        $this->bigIntegerFactory = new BigIntegerFactory();
    }

    public function buildCertificateA(string $publicKey, string $privateKey, string $content = null): Certificate
    {
        return new Certificate(Certificate::TYPE_A, $publicKey, $privateKey, $content);
    }

    public function buildCertificateE(
        string $publicKey,
        string $privateKey = null,
        string $content = null
    ): Certificate {
        return new Certificate(Certificate::TYPE_E, $publicKey, $privateKey, $content);
    }

    public function buildCertificateX(
        string $publicKey,
        string $privateKey = null,
        string $content = null
    ): Certificate {
        return new Certificate(Certificate::TYPE_X, $publicKey, $privateKey, $content);
    }

    public function generateCertificateAFromKeys(array $keys, bool $isCertified): Certificate
    {
        return $this->generateCertificateFromKeys($keys, Certificate::TYPE_A, $isCertified);
    }

    public function generateCertificateEFromKeys(array $keys, bool $isCertified): Certificate
    {
        return $this->generateCertificateFromKeys($keys, Certificate::TYPE_E, $isCertified);
    }

    public function generateCertificateXFromKeys(array $keys, bool $isCertified): Certificate
    {
        return $this->generateCertificateFromKeys($keys, Certificate::TYPE_X, $isCertified);
    }

    public function buildCertificateEFromDetails(
        string $exponent,
        string $modulus,
        string $content = null
    ): Certificate {
        return $this->buildCertificateFromDetails(Certificate::TYPE_E, $exponent, $modulus, $content);
    }

    public function buildCertificateXFromDetails(
        string $exponent,
        string $modulus,
        string $content = null
    ): Certificate {
        return $this->buildCertificateFromDetails(Certificate::TYPE_X, $exponent, $modulus, $content);
    }

    private function generateCertificateFromKeys(array $keys, string $type, bool $isCertified): Certificate
    {
        if ($isCertified) {
            $certificateContent = $this->generateCertificateContent($keys, $type);
        }

        return new Certificate($type, $keys['publickey'], $keys['privatekey'], $certificateContent ?? null);
    }

    private function generateCertificateContent(array $keys, string $type): string
    {
        $privateKey = $this->rsaFactory->create();
        $privateKey->loadKey($keys['privatekey']);

        $publicKey = $this->rsaFactory->create();
        $publicKey->loadKey($keys['publickey']);
        $publicKey->setPublicKey();

        $generator = X509GeneratorFactory::create();

        return $generator->generateX509($privateKey, $publicKey, [
            'type' => $type,
        ]);
    }

    private function buildCertificateFromDetails(
        string $type,
        string $exponent,
        string $modulus,
        string $content = null
    ): Certificate {
        $rsa = $this->rsaFactory->create();
        $rsa->loadKey([
            'n' => $this->bigIntegerFactory->create($modulus, 256),
            'e' => $this->bigIntegerFactory->create($exponent, 256),
        ]);
        $publicKey = $rsa->getPublicKey(RSA::PUBLIC_FORMAT_PKCS1);
        $privateKey = $rsa->getPrivateKey(RSA::PUBLIC_FORMAT_PKCS1);

        return new Certificate($type, $publicKey, $privateKey, $content);
    }
}

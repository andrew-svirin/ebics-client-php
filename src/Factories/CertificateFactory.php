<?php

namespace AndrewSvirin\Ebics\Factories;

use AndrewSvirin\Ebics\Contracts\X509GeneratorInterface;
use AndrewSvirin\Ebics\Factories\Crypt\BigIntegerFactory;
use AndrewSvirin\Ebics\Factories\Crypt\RSAFactory;
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

    /**
     * @param string $publicKey
     * @param string $privateKey
     * @param string|null $content
     *
     * @return Certificate
     */
    public function buildCertificateA(string $publicKey, string $privateKey, string $content = null): Certificate
    {
        return new Certificate(Certificate::TYPE_A, $publicKey, $privateKey, $content);
    }

    /**
     * @param string $publicKey
     * @param string|null $privateKey
     * @param string|null $content
     *
     * @return Certificate
     */
    public function buildCertificateE(
        string $publicKey,
        string $privateKey = null,
        string $content = null
    ): Certificate {
        return new Certificate(Certificate::TYPE_E, $publicKey, $privateKey, $content);
    }

    /**
     * @param string $publicKey
     * @param string|null $privateKey
     * @param string|null $content
     *
     * @return Certificate
     */
    public function buildCertificateX(
        string $publicKey,
        string $privateKey = null,
        string $content = null
    ): Certificate {
        return new Certificate(Certificate::TYPE_X, $publicKey, $privateKey, $content);
    }

    /**
     * @param array $keys
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @return Certificate
     */
    public function generateCertificateAFromKeys(array $keys, X509GeneratorInterface $x509Generator = null): Certificate
    {
        return $this->generateCertificateFromKeys($keys, Certificate::TYPE_A, $x509Generator);
    }

    /**
     * @param array $keys
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @return Certificate
     */
    public function generateCertificateEFromKeys(array $keys, X509GeneratorInterface $x509Generator = null): Certificate
    {
        return $this->generateCertificateFromKeys($keys, Certificate::TYPE_E, $x509Generator);
    }

    /**
     * @param array $keys
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @return Certificate
     */
    public function generateCertificateXFromKeys(array $keys, X509GeneratorInterface $x509Generator = null): Certificate
    {
        return $this->generateCertificateFromKeys($keys, Certificate::TYPE_X, $x509Generator);
    }

    /**
     * @param string $exponent
     * @param string $modulus
     * @param string|null $content
     *
     * @return Certificate
     */
    public function buildCertificateEFromDetails(
        string $exponent,
        string $modulus,
        string $content = null
    ): Certificate {
        return $this->buildCertificateFromDetails(Certificate::TYPE_E, $exponent, $modulus, $content);
    }

    /**
     * @param string $exponent
     * @param string $modulus
     * @param string|null $content
     *
     * @return Certificate
     */
    public function buildCertificateXFromDetails(
        string $exponent,
        string $modulus,
        string $content = null
    ): Certificate {
        return $this->buildCertificateFromDetails(Certificate::TYPE_X, $exponent, $modulus, $content);
    }

    /**
     * @param array $keys
     * @param string $type
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @return Certificate
     */
    private function generateCertificateFromKeys(
        array $keys,
        string $type,
        X509GeneratorInterface $x509Generator = null
    ): Certificate {
        if (null !== $x509Generator) {
            $certificateContent = $this->generateCertificateContent($keys, $type, $x509Generator);
        }

        return new Certificate(
            $type,
            $keys['publickey'],
            $keys['privatekey'],
            $certificateContent ?? null
        );
    }

    /**
     * @param array $keys
     * @param string $type
     * @param X509GeneratorInterface $x509Generator
     *
     * @return string
     */
    private function generateCertificateContent(
        array $keys,
        string $type,
        X509GeneratorInterface $x509Generator
    ): string {
        $privateKey = $this->rsaFactory->create();
        $privateKey->loadKey($keys['privatekey']);

        $publicKey = $this->rsaFactory->create();
        $publicKey->loadKey($keys['publickey']);
        $publicKey->setPublicKey();

        return $x509Generator->generateX509($privateKey, $publicKey, [
            'type' => $type,
        ]);
    }

    /**
     * @param string $type
     * @param string $exponent
     * @param string $modulus
     * @param string|null $content
     *
     * @return Certificate
     */
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

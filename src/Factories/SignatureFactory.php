<?php

namespace AndrewSvirin\Ebics\Factories;

use AndrewSvirin\Ebics\Contracts\SignatureInterface;
use AndrewSvirin\Ebics\Contracts\X509GeneratorInterface;
use AndrewSvirin\Ebics\Factories\Crypt\BigIntegerFactory;
use AndrewSvirin\Ebics\Factories\Crypt\RSAFactory;
use AndrewSvirin\Ebics\Models\Crypt\RSA;
use AndrewSvirin\Ebics\Models\Signature;
use LogicException;

/**
 * Class SignatureFactory represents producers for the @see Signature.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin, Guillaume Sainthillier
 */
class SignatureFactory
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
     * @param string $type
     * @param string $publicKey
     * @param string|null $privateKey
     *
     * @return SignatureInterface
     */
    public function create(string $type, string $publicKey, string $privateKey = null): SignatureInterface
    {
        switch ($type) {
            case Signature::TYPE_A:
                $signature = $this->createSignatureA($publicKey, $privateKey);
                break;
            case Signature::TYPE_E:
                $signature = $this->createSignatureE($publicKey, $privateKey);
                break;
            case Signature::TYPE_X:
                $signature = $this->createSignatureX($publicKey, $privateKey);
                break;
            default:
                throw new LogicException('Unpredictable case.');
        }

        return $signature;
    }

    /**
     * @param string $publicKey
     * @param string $privateKey
     *
     * @return SignatureInterface
     */
    public function createSignatureA(string $publicKey, string $privateKey): SignatureInterface
    {
        return new Signature(Signature::TYPE_A, $publicKey, $privateKey);
    }

    /**
     * @param string $publicKey
     * @param string|null $privateKey
     *
     * @return SignatureInterface
     */
    public function createSignatureE(string $publicKey, string $privateKey = null): SignatureInterface
    {
        return new Signature(Signature::TYPE_E, $publicKey, $privateKey);
    }

    /**
     * @param string $publicKey
     * @param string|null $privateKey
     *
     * @return SignatureInterface
     */
    public function createSignatureX(string $publicKey, string $privateKey = null): SignatureInterface
    {
        return new Signature(Signature::TYPE_X, $publicKey, $privateKey);
    }

    /**
     * @param array $keys = [
     *      'publickey' => '<string>',
     *      'privatekey' => '<string>',
     *  ]
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @return SignatureInterface
     */
    public function createSignatureAFromKeys(
        array $keys,
        X509GeneratorInterface $x509Generator = null
    ): SignatureInterface {
        return $this->createSignatureFromKeys($keys, Signature::TYPE_A, $x509Generator);
    }

    /**
     * @param array $keys = [
     *      'publickey' => '<string>',
     *      'privatekey' => '<string>',
     *  ]
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @return SignatureInterface
     */
    public function createSignatureEFromKeys(
        array $keys,
        X509GeneratorInterface $x509Generator = null
    ): SignatureInterface {
        return $this->createSignatureFromKeys($keys, Signature::TYPE_E, $x509Generator);
    }

    /**
     * @param array $keys = [
     *      'publickey' => '<string>',
     *      'privatekey' => '<string>',
     *  ]
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @return SignatureInterface
     */
    public function createSignatureXFromKeys(
        array $keys,
        X509GeneratorInterface $x509Generator = null
    ): SignatureInterface {
        return $this->createSignatureFromKeys($keys, Signature::TYPE_X, $x509Generator);
    }

    /**
     * @param string $exponent
     * @param string $modulus
     *
     * @return SignatureInterface
     */
    public function createCertificateEFromDetails(string $exponent, string $modulus): SignatureInterface
    {
        return $this->createCertificateFromDetails(Signature::TYPE_E, $exponent, $modulus);
    }

    /**
     * @param string $exponent
     * @param string $modulus
     *
     * @return SignatureInterface
     */
    public function createCertificateXFromDetails(string $exponent, string $modulus): SignatureInterface
    {
        return $this->createCertificateFromDetails(Signature::TYPE_X, $exponent, $modulus);
    }

    /**
     * @param array $keys = [
     *      'publickey' => '<string>',
     *      'privatekey' => '<string>',
     *  ]
     * @param string $type
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @return SignatureInterface
     */
    private function createSignatureFromKeys(
        array $keys,
        string $type,
        X509GeneratorInterface $x509Generator = null
    ): SignatureInterface {
        $signature = new Signature(
            $type,
            $keys['publickey'],
            $keys['privatekey']
        );

        if (null !== $x509Generator) {
            $signature->setCertificateContent($this->generateCertificateContent($keys, $type, $x509Generator));
        }

        return $signature;
    }

    /**
     * @param array $keys = [
     *      'publickey' => '<string>',
     *      'privatekey' => '<string>',
     *  ]
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
     *
     * @return SignatureInterface
     */
    private function createCertificateFromDetails(
        string $type,
        string $exponent,
        string $modulus
    ): SignatureInterface {
        $rsa = $this->rsaFactory->create();
        $rsa->loadKey([
            'n' => $this->bigIntegerFactory->create($modulus, 256),
            'e' => $this->bigIntegerFactory->create($exponent, 256),
        ]);
        $publicKey = $rsa->getPublicKey(RSA::PUBLIC_FORMAT_PKCS1);
        $privateKey = $rsa->getPrivateKey(RSA::PUBLIC_FORMAT_PKCS1);

        return new Signature($type, $publicKey, $privateKey);
    }
}

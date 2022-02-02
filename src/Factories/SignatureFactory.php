<?php

namespace AndrewSvirin\Ebics\Factories;

use AndrewSvirin\Ebics\Contracts\Crypt\BigIntegerInterface;
use AndrewSvirin\Ebics\Contracts\SignatureInterface;
use AndrewSvirin\Ebics\Contracts\X509GeneratorInterface;
use AndrewSvirin\Ebics\Factories\Crypt\RSAFactory;
use AndrewSvirin\Ebics\Models\Crypt\RSA;
use AndrewSvirin\Ebics\Models\Signature;
use LogicException;
use RuntimeException;

/**
 * Class SignatureFactory represents producers for the @see Signature.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin, Guillaume Sainthillier
 */
final class SignatureFactory
{
    /**
     * @var RSAFactory
     */
    private $rsaFactory;

    public function __construct()
    {
        $this->rsaFactory = new RSAFactory();
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
            case SignatureInterface::TYPE_A:
                $signature = $this->createSignatureA($publicKey, $privateKey);
                break;
            case SignatureInterface::TYPE_E:
                $signature = $this->createSignatureE($publicKey, $privateKey);
                break;
            case SignatureInterface::TYPE_X:
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
        return new Signature(SignatureInterface::TYPE_A, $publicKey, $privateKey);
    }

    /**
     * @param string $publicKey
     * @param string|null $privateKey
     *
     * @return SignatureInterface
     */
    public function createSignatureE(string $publicKey, string $privateKey = null): SignatureInterface
    {
        return new Signature(SignatureInterface::TYPE_E, $publicKey, $privateKey);
    }

    /**
     * @param string $publicKey
     * @param string|null $privateKey
     *
     * @return SignatureInterface
     */
    public function createSignatureX(string $publicKey, string $privateKey = null): SignatureInterface
    {
        return new Signature(SignatureInterface::TYPE_X, $publicKey, $privateKey);
    }

    /**
     * @param array $keys = [
     *      'publickey' => '<string>',
     *      'privatekey' => '<string>',
     *  ]
     * @param string $password
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @return SignatureInterface
     */
    public function createSignatureAFromKeys(
        array $keys,
        string $password,
        X509GeneratorInterface $x509Generator = null
    ): SignatureInterface {
        return $this->createSignatureFromKeys($keys, $password, SignatureInterface::TYPE_A, $x509Generator);
    }

    /**
     * @param array $keys = [
     *      'publickey' => '<string>',
     *      'privatekey' => '<string>',
     *  ]
     * @param string $password
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @return SignatureInterface
     */
    public function createSignatureEFromKeys(
        array $keys,
        string $password,
        X509GeneratorInterface $x509Generator = null
    ): SignatureInterface {
        return $this->createSignatureFromKeys($keys, $password, SignatureInterface::TYPE_E, $x509Generator);
    }

    /**
     * @param array $keys = [
     *      'publickey' => '<string>',
     *      'privatekey' => '<string>',
     *  ]
     * @param string $password
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @return SignatureInterface
     */
    public function createSignatureXFromKeys(
        array $keys,
        string $password,
        X509GeneratorInterface $x509Generator = null
    ): SignatureInterface {
        return $this->createSignatureFromKeys($keys, $password, SignatureInterface::TYPE_X, $x509Generator);
    }

    /**
     * @param BigIntegerInterface $exponent
     * @param BigIntegerInterface $modulus
     *
     * @return SignatureInterface
     */
    public function createCertificateEFromDetails(
        BigIntegerInterface $exponent,
        BigIntegerInterface $modulus
    ): SignatureInterface {
        return $this->createCertificateFromDetails(SignatureInterface::TYPE_E, $exponent, $modulus);
    }

    /**
     * @param BigIntegerInterface $exponent
     * @param BigIntegerInterface $modulus
     *
     * @return SignatureInterface
     */
    public function createCertificateXFromDetails(
        BigIntegerInterface $exponent,
        BigIntegerInterface $modulus
    ): SignatureInterface {
        return $this->createCertificateFromDetails(SignatureInterface::TYPE_X, $exponent, $modulus);
    }

    /**
     * @param array $keys = [
     *      'publickey' => '<string>',
     *      'privatekey' => '<string>',
     *  ]
     * @param string $password
     * @param string $type
     * @param X509GeneratorInterface|null $x509Generator
     *
     * @return SignatureInterface
     */
    private function createSignatureFromKeys(
        array $keys,
        string $password,
        string $type,
        X509GeneratorInterface $x509Generator = null
    ): SignatureInterface {
        $signature = new Signature($type, $keys['publickey'], $keys['privatekey']);

        if (null !== $x509Generator) {
            $certificateContent = $this->generateCertificateContent($keys, $password, $type, $x509Generator);
            $signature->setCertificateContent($certificateContent);
        }

        return $signature;
    }

    /**
     * @param array $keys = [
     *      'publickey' => '<string>',
     *      'privatekey' => '<string>',
     *  ]
     * @param string $password
     * @param string $type
     * @param X509GeneratorInterface $x509Generator
     *
     * @return string
     */
    private function generateCertificateContent(
        array $keys,
        string $password,
        string $type,
        X509GeneratorInterface $x509Generator
    ): string {
        $rsaPrivateKey = $this->rsaFactory->createPrivate($keys['privatekey'], $password);

        $rsaPublicKey = $this->rsaFactory->createPublic($keys['publickey']);

        switch ($type) {
            case SignatureInterface::TYPE_A:
                $x509 = $x509Generator->generateAX509($rsaPrivateKey, $rsaPublicKey);
                break;
            case SignatureInterface::TYPE_E:
                $x509 = $x509Generator->generateEX509($rsaPrivateKey, $rsaPublicKey);
                break;
            case SignatureInterface::TYPE_X:
                $x509 = $x509Generator->generateXX509($rsaPrivateKey, $rsaPublicKey);
                break;
            default:
                throw new RuntimeException('Unpredictable type.');
        }

        if (!($currentCert = $x509->saveX509CurrentCert())) {
            throw new RuntimeException('Can not save current certificate.');
        }

        return $currentCert;
    }

    /**
     * @param string $type
     * @param BigIntegerInterface $exponent
     * @param BigIntegerInterface $modulus
     *
     * @return SignatureInterface
     */
    private function createCertificateFromDetails(
        string $type,
        BigIntegerInterface $exponent,
        BigIntegerInterface $modulus
    ): SignatureInterface {
        $details = [
            'modulus' => $modulus,
            'exponent' => $exponent,
        ];
        $rsa = $this->rsaFactory->createPublic($details);
        $publicKey = $rsa->getPublicKey(RSA::PUBLIC_FORMAT_PKCS1);

        return new Signature($type, $publicKey, null);
    }
}

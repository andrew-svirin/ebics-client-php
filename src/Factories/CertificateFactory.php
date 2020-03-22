<?php

namespace AndrewSvirin\Ebics\Factories;

use AndrewSvirin\Ebics\Factories\X509\X509GeneratorFactory;
use AndrewSvirin\Ebics\Models\Certificate;
use phpseclib\Crypt\RSA;
use phpseclib\Math\BigInteger;

/**
 * Class CertificateFactory represents producers for the @see Certificate.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin, Guillaume Sainthillier
 */
class CertificateFactory
{
    public static function buildCertificateA(string $publicKey, string $privateKey, string $content = null): Certificate
    {
        return new Certificate(Certificate::TYPE_A, $publicKey, $privateKey, $content);
    }

    public static function buildCertificateE(string $publicKey, string $privateKey = null, string $content = null): Certificate
    {
        return new Certificate(Certificate::TYPE_E, $publicKey, $privateKey, $content);
    }

    public static function buildCertificateX(string $publicKey, string $privateKey = null, string $content = null): Certificate
    {
        return new Certificate(Certificate::TYPE_X, $publicKey, $privateKey, $content);
    }

    public static function generateCertificateAFromKeys(array $keys, bool $isCertified): Certificate
    {
        return self::generateCertificateFromKeys($keys, Certificate::TYPE_A, $isCertified);
    }

    public static function generateCertificateEFromKeys(array $keys, bool $isCertified): Certificate
    {
        return self::generateCertificateFromKeys($keys, Certificate::TYPE_E, $isCertified);
    }

    public static function generateCertificateXFromKeys(array $keys, bool $isCertified): Certificate
    {
        return self::generateCertificateFromKeys($keys, Certificate::TYPE_X, $isCertified);
    }

    public static function buildCertificateEFromDetails(string $exponent, string $modulus, string $content = null): Certificate
    {
        return self::buildCertificateFromDetails(Certificate::TYPE_E, $exponent, $modulus, $content);
    }

    public static function buildCertificateXFromDetails(string $exponent, string $modulus, string $content = null): Certificate
    {
        return self::buildCertificateFromDetails(Certificate::TYPE_X, $exponent, $modulus, $content);
    }

    private static function generateCertificateFromKeys(array $keys, string $type, bool $isCertified): Certificate
    {
        if ($isCertified) {
            $certificateContent = self::generateCertificateContent($keys, $type);
        }
        return new Certificate($type, $keys['publickey'], $keys['privatekey'], $certificateContent ?? null);
    }

    private static function generateCertificateContent(array $keys, string $type): string
    {
        $privateKey = new RSA();
        $privateKey->loadKey($keys['privatekey']);

        $publicKey = new RSA();
        $publicKey->loadKey($keys['publickey']);
        $publicKey->setPublicKey();

        $generator = X509GeneratorFactory::create();
        return $generator->generateX509($privateKey, $publicKey, [
            'type' => $type,
        ]);
    }

    private static function buildCertificateFromDetails(string $type, string $exponent, string $modulus, string $content = null): Certificate
    {
        $rsa = new RSA();
        $rsa->loadKey([
            'n' => new BigInteger($modulus, 256),
            'e' => new BigInteger($exponent, 256),
        ]);
        $publicKey = $rsa->getPublicKey(RSA::PUBLIC_FORMAT_PKCS1);
        $privateKey = $rsa->getPrivateKey(RSA::PUBLIC_FORMAT_PKCS1);
        return new Certificate($type, $publicKey, $privateKey, $content);
    }
}
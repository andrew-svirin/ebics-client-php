<?php

namespace AndrewSvirin\Ebics\Factories;

use AndrewSvirin\Ebics\Models\KeyRing;

/**
 * Class KeyRingFactory represents producers for the @see KeyRing.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class KeyRingFactory
{
    const USER_PREFIX = 'USER';
    const BANK_PREFIX = 'BANK';
    const CERTIFICATE_PREFIX_A = 'A';
    const CERTIFICATE_PREFIX_X = 'X';
    const CERTIFICATE_PREFIX_E = 'E';
    const CERTIFICATE_PREFIX = 'CERTIFICATE';
    const PUBLIC_KEY_PREFIX = 'PUBLIC_KEY';
    const PRIVATE_KEY_PREFIX = 'PRIVATE_KEY';

    public static function buildKeyRingFromData(array $data): KeyRing
    {
        $toCheck = [
            self::USER_PREFIX => [
                self::CERTIFICATE_PREFIX_A => ['build' => 'buildCertificateA', 'setter' => 'setUserCertificateA'],
                self::CERTIFICATE_PREFIX_E => ['build' => 'buildCertificateE', 'setter' => 'setUserCertificateE'],
                self::CERTIFICATE_PREFIX_X => ['build' => 'buildCertificateX', 'setter' => 'setUserCertificateX'],
            ],
            self::BANK_PREFIX => [
                self::CERTIFICATE_PREFIX_E => ['build' => 'buildCertificateE', 'setter' => 'setBankCertificateE'],
                self::CERTIFICATE_PREFIX_X => ['build' => 'buildCertificateX', 'setter' => 'setBankCertificateX'],
            ]
        ];

        $keyRing = new KeyRing();

        foreach ($toCheck as $prefix => $certificatList) {
            foreach ($certificatList as $certificateName => $methods) {
                $build = $methods['build'];
                $setter = $methods['setter'];

                if (!empty($data[$prefix][$certificateName][self::PUBLIC_KEY_PREFIX])) {
                    $content = $data[$prefix][$certificateName][self::CERTIFICATE_PREFIX];
                    $publicKey = $data[$prefix][$certificateName][self::PUBLIC_KEY_PREFIX];
                    $privateKey = $data[$prefix][$certificateName][self::PRIVATE_KEY_PREFIX];
                    $certificate = CertificateFactory::$build(
                        self::decodeValue($publicKey),
                        self::decodeValue($privateKey),
                        !empty($content) ? self::decodeValue($content) : null
                    );
                    $keyRing->$setter($certificate);
                }
            }
        }

        return $keyRing;
    }

    public static function buildDataFromKeyRing(KeyRing $keyRing): array
    {
        if (null !== $keyRing->getUserCertificateA()) {
            $userCertificateAB64 = $keyRing->getUserCertificateA()->getContent();
            $userCertificateAPublicKey = $keyRing->getUserCertificateA()->getPublicKey();
            $userCertificateAPrivateKey = $keyRing->getUserCertificateA()->getPrivateKey();
        }
        if (null !== $keyRing->getUserCertificateE()) {
            $userCertificateEB64 = $keyRing->getUserCertificateE()->getContent();
            $userCertificateEPublicKey = $keyRing->getUserCertificateE()->getPublicKey();
            $userCertificateEPrivateKey = $keyRing->getUserCertificateE()->getPrivateKey();
        }
        if (null !== $keyRing->getUserCertificateX()) {
            $userCertificateXB64 = $keyRing->getUserCertificateX()->getContent();
            $userCertificateXPublicKey = $keyRing->getUserCertificateX()->getPublicKey();
            $userCertificateXPrivateKey = $keyRing->getUserCertificateX()->getPrivateKey();
        }
        if (null !== $keyRing->getBankCertificateE()) {
            $bankCertificateEB64 = $keyRing->getBankCertificateE()->getContent();
            $bankCertificateEPublicKey = $keyRing->getBankCertificateE()->getPublicKey();
            $bankCertificateEPrivateKey = $keyRing->getBankCertificateE()->getPrivateKey();
        }
        if (null !== $keyRing->getBankCertificateX()) {
            $bankCertificateXB64 = $keyRing->getBankCertificateX()->getContent();
            $bankCertificateXPublicKey = $keyRing->getBankCertificateX()->getPublicKey();
            $bankCertificateXPrivateKey = $keyRing->getBankCertificateX()->getPrivateKey();
        }

        return [
         self::USER_PREFIX => [
            self::CERTIFICATE_PREFIX_A => [
               self::CERTIFICATE_PREFIX => isset($userCertificateAB64) ? self::encodeValue($userCertificateAB64) : null,
               self::PUBLIC_KEY_PREFIX => isset($userCertificateAPublicKey) ? self::encodeValue($userCertificateAPublicKey) : null,
               self::PRIVATE_KEY_PREFIX => isset($userCertificateAPrivateKey) ? self::encodeValue($userCertificateAPrivateKey) : null,
            ],
            self::CERTIFICATE_PREFIX_E => [
               self::CERTIFICATE_PREFIX => isset($userCertificateEB64) ? self::encodeValue($userCertificateEB64) : null,
               self::PUBLIC_KEY_PREFIX => isset($userCertificateEPublicKey) ? self::encodeValue($userCertificateEPublicKey) : null,
               self::PRIVATE_KEY_PREFIX => isset($userCertificateEPrivateKey) ? self::encodeValue($userCertificateEPrivateKey) : null,
            ],
            self::CERTIFICATE_PREFIX_X => [
               self::CERTIFICATE_PREFIX => isset($userCertificateXB64) ? self::encodeValue($userCertificateXB64) : null,
               self::PUBLIC_KEY_PREFIX => isset($userCertificateXPublicKey) ? self::encodeValue($userCertificateXPublicKey) : null,
               self::PRIVATE_KEY_PREFIX => isset($userCertificateXPrivateKey) ? self::encodeValue($userCertificateXPrivateKey) : null,
            ],
         ],
         self::BANK_PREFIX => [
            self::CERTIFICATE_PREFIX_E => [
               self::CERTIFICATE_PREFIX => isset($bankCertificateEB64) ? self::encodeValue($bankCertificateEB64) : null,
               self::PUBLIC_KEY_PREFIX => isset($bankCertificateEPublicKey) ? self::encodeValue($bankCertificateEPublicKey) : null,
               self::PRIVATE_KEY_PREFIX => isset($bankCertificateEPrivateKey) ? self::encodeValue($bankCertificateEPrivateKey) : null,
            ],
            self::CERTIFICATE_PREFIX_X => [
               self::CERTIFICATE_PREFIX => isset($bankCertificateXB64) ? self::encodeValue($bankCertificateXB64) : null,
               self::PUBLIC_KEY_PREFIX => isset($bankCertificateXPublicKey) ? self::encodeValue($bankCertificateXPublicKey) : null,
               self::PRIVATE_KEY_PREFIX => isset($bankCertificateXPrivateKey) ? self::encodeValue($bankCertificateXPrivateKey) : null,
            ],
         ],
      ];
    }

    private static function encodeValue(string $value) : string
    {
        return base64_encode($value);
    }

    private static function decodeValue(string $value) : string
    {
        return base64_decode($value);
    }
}

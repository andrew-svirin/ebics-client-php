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
        $keyRing = new KeyRing();
        if (!empty($data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_A][self::PUBLIC_KEY_PREFIX])) {
            $userCertificateAContent = $data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_A][self::CERTIFICATE_PREFIX];
            $userCertificateAPublicKey = $data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_A][self::PUBLIC_KEY_PREFIX];
            $userCertificateAPrivateKey = $data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_A][self::PRIVATE_KEY_PREFIX];
            $userCertificateA = CertificateFactory::buildCertificateA(
            self::decodeValue($userCertificateAPublicKey),
            self::decodeValue($userCertificateAPrivateKey),
            !empty($userCertificateAContent) ? self::decodeValue($userCertificateAContent) : null
         );
            $keyRing->setUserCertificateA($userCertificateA);
        }
        if (!empty($data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_E][self::PUBLIC_KEY_PREFIX])) {
            $userCertificateEContent = $data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_E][self::CERTIFICATE_PREFIX];
            $userCertificateEPublicKey = $data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_E][self::PUBLIC_KEY_PREFIX];
            $userCertificateEPrivateKey = $data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_E][self::PRIVATE_KEY_PREFIX];
            $userCertificateE = CertificateFactory::buildCertificateE(
            self::decodeValue($userCertificateEPublicKey),
            self::decodeValue($userCertificateEPrivateKey),
            !empty($userCertificateEContent) ? self::decodeValue($userCertificateEContent) : null
         );
            $keyRing->setUserCertificateE($userCertificateE);
        }
        if (!empty($data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_X][self::PUBLIC_KEY_PREFIX])) {
            $userCertificateXContent = $data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_X][self::CERTIFICATE_PREFIX];
            $userCertificateXPublicKey = $data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_X][self::PUBLIC_KEY_PREFIX];
            $userCertificateXPrivateKey = $data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_X][self::PRIVATE_KEY_PREFIX];
            $userCertificateX = CertificateFactory::buildCertificateX(
            self::decodeValue($userCertificateXPublicKey),
            self::decodeValue($userCertificateXPrivateKey),
            !empty($userCertificateXContent) ? self::decodeValue($userCertificateXContent) : null
         );
            $keyRing->setUserCertificateX($userCertificateX);
        }
        if (!empty($data[self::BANK_PREFIX][self::CERTIFICATE_PREFIX_E][self::PUBLIC_KEY_PREFIX])) {
            $bankCertificateEContent = $data[self::BANK_PREFIX][self::CERTIFICATE_PREFIX_E][self::CERTIFICATE_PREFIX];
            $bankCertificateEPublicKey = $data[self::BANK_PREFIX][self::CERTIFICATE_PREFIX_E][self::PUBLIC_KEY_PREFIX];
            $bankCertificateEPrivateKey = $data[self::BANK_PREFIX][self::CERTIFICATE_PREFIX_E][self::PRIVATE_KEY_PREFIX];
            $bankCertificateE = CertificateFactory::buildCertificateE(
            self::decodeValue($bankCertificateEPublicKey),
            !empty($bankCertificateEPrivateKey) ? self::decodeValue($bankCertificateEPrivateKey) : null,
            !empty($bankCertificateEContent) ? self::decodeValue($bankCertificateEContent) : null
         );
            $keyRing->setBankCertificateE($bankCertificateE);
        }
        if (!empty($data[self::BANK_PREFIX][self::CERTIFICATE_PREFIX_X][self::PUBLIC_KEY_PREFIX])) {
            $bankCertificateXContent = $data[self::BANK_PREFIX][self::CERTIFICATE_PREFIX_X][self::CERTIFICATE_PREFIX];
            $bankCertificateXPublicKey = $data[self::BANK_PREFIX][self::CERTIFICATE_PREFIX_X][self::PUBLIC_KEY_PREFIX];
            $bankCertificateXPrivateKey = $data[self::BANK_PREFIX][self::CERTIFICATE_PREFIX_X][self::PRIVATE_KEY_PREFIX];
            $bankCertificateX = CertificateFactory::buildCertificateX(
            self::decodeValue($bankCertificateXPublicKey),
            !empty($bankCertificateXPrivateKey) ? self::decodeValue($bankCertificateXPrivateKey) : null,
            !empty($bankCertificateXContent) ? self::decodeValue($bankCertificateXContent) : null
         );
            $keyRing->setBankCertificateX($bankCertificateX);
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

    private static function encodeValue($value)
    {
        return base64_encode($value);
    }

    private static function decodeValue($value)
    {
        return base64_decode($value);
    }
}

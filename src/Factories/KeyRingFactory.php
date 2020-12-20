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

    /**
     * @var CertificateFactory
     */
    private $certificateFactory;

    public function __construct()
    {
        $this->certificateFactory = new CertificateFactory();
    }

    public function buildKeyRingFromData(array $data): KeyRing
    {
        $keyRing = new KeyRing();
        if (!empty($data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_A][self::PUBLIC_KEY_PREFIX])) {
            $userCertificateAContent =
                $data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_A][self::CERTIFICATE_PREFIX];
            $userCertificateAPublicKey =
                $data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_A][self::PUBLIC_KEY_PREFIX];
            $userCertificateAPrivateKey =
                $data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_A][self::PRIVATE_KEY_PREFIX];
            $userCertificateA = $this->certificateFactory->buildCertificateA(
                $this->decodeValue($userCertificateAPublicKey),
                $this->decodeValue($userCertificateAPrivateKey),
                !empty($userCertificateAContent) ? $this->decodeValue($userCertificateAContent) : null
            );
            $keyRing->setUserCertificateA($userCertificateA);
        }
        if (!empty($data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_E][self::PUBLIC_KEY_PREFIX])) {
            $userCertificateEContent =
                $data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_E][self::CERTIFICATE_PREFIX];
            $userCertificateEPublicKey =
                $data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_E][self::PUBLIC_KEY_PREFIX];
            $userCertificateEPrivateKey =
                $data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_E][self::PRIVATE_KEY_PREFIX];
            $userCertificateE = $this->certificateFactory->buildCertificateE(
                $this->decodeValue($userCertificateEPublicKey),
                $this->decodeValue($userCertificateEPrivateKey),
                !empty($userCertificateEContent) ? $this->decodeValue($userCertificateEContent) : null
            );
            $keyRing->setUserCertificateE($userCertificateE);
        }
        if (!empty($data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_X][self::PUBLIC_KEY_PREFIX])) {
            $userCertificateXContent =
                $data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_X][self::CERTIFICATE_PREFIX];
            $userCertificateXPublicKey =
                $data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_X][self::PUBLIC_KEY_PREFIX];
            $userCertificateXPrivateKey =
                $data[self::USER_PREFIX][self::CERTIFICATE_PREFIX_X][self::PRIVATE_KEY_PREFIX];
            $userCertificateX = $this->certificateFactory->buildCertificateX(
                $this->decodeValue($userCertificateXPublicKey),
                $this->decodeValue($userCertificateXPrivateKey),
                !empty($userCertificateXContent) ? $this->decodeValue($userCertificateXContent) : null
            );
            $keyRing->setUserCertificateX($userCertificateX);
        }
        if (!empty($data[self::BANK_PREFIX][self::CERTIFICATE_PREFIX_E][self::PUBLIC_KEY_PREFIX])) {
            $bankCertificateEContent =
                $data[self::BANK_PREFIX][self::CERTIFICATE_PREFIX_E][self::CERTIFICATE_PREFIX];
            $bankCertificateEPublicKey =
                $data[self::BANK_PREFIX][self::CERTIFICATE_PREFIX_E][self::PUBLIC_KEY_PREFIX];
            $bankCertificateEPrivateKey =
                $data[self::BANK_PREFIX][self::CERTIFICATE_PREFIX_E][self::PRIVATE_KEY_PREFIX];
            $bankCertificateE = $this->certificateFactory->buildCertificateE(
                $this->decodeValue($bankCertificateEPublicKey),
                !empty($bankCertificateEPrivateKey) ? $this->decodeValue($bankCertificateEPrivateKey) : null,
                !empty($bankCertificateEContent) ? $this->decodeValue($bankCertificateEContent) : null
            );
            $keyRing->setBankCertificateE($bankCertificateE);
        }
        if (!empty($data[self::BANK_PREFIX][self::CERTIFICATE_PREFIX_X][self::PUBLIC_KEY_PREFIX])) {
            $bankCertificateXContent =
                $data[self::BANK_PREFIX][self::CERTIFICATE_PREFIX_X][self::CERTIFICATE_PREFIX];
            $bankCertificateXPublicKey =
                $data[self::BANK_PREFIX][self::CERTIFICATE_PREFIX_X][self::PUBLIC_KEY_PREFIX];
            $bankCertificateXPrivateKey =
                $data[self::BANK_PREFIX][self::CERTIFICATE_PREFIX_X][self::PRIVATE_KEY_PREFIX];
            $bankCertificateX = $this->certificateFactory->buildCertificateX(
                $this->decodeValue($bankCertificateXPublicKey),
                !empty($bankCertificateXPrivateKey) ? $this->decodeValue($bankCertificateXPrivateKey) : null,
                !empty($bankCertificateXContent) ? $this->decodeValue($bankCertificateXContent) : null
            );
            $keyRing->setBankCertificateX($bankCertificateX);
        }

        return $keyRing;
    }

    public function buildDataFromKeyRing(KeyRing $keyRing): array
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
                    self::CERTIFICATE_PREFIX => isset($userCertificateAB64) ?
                        $this->encodeValue($userCertificateAB64) : null,
                    self::PUBLIC_KEY_PREFIX => isset($userCertificateAPublicKey) ?
                        $this->encodeValue($userCertificateAPublicKey) : null,
                    self::PRIVATE_KEY_PREFIX => isset($userCertificateAPrivateKey) ?
                        $this->encodeValue($userCertificateAPrivateKey) : null,
                ],
                self::CERTIFICATE_PREFIX_E => [
                    self::CERTIFICATE_PREFIX => isset($userCertificateEB64) ?
                        $this->encodeValue($userCertificateEB64) : null,
                    self::PUBLIC_KEY_PREFIX => isset($userCertificateEPublicKey) ?
                        $this->encodeValue($userCertificateEPublicKey) : null,
                    self::PRIVATE_KEY_PREFIX => isset($userCertificateEPrivateKey) ?
                        $this->encodeValue($userCertificateEPrivateKey) : null,
                ],
                self::CERTIFICATE_PREFIX_X => [
                    self::CERTIFICATE_PREFIX => isset($userCertificateXB64) ?
                        $this->encodeValue($userCertificateXB64) : null,
                    self::PUBLIC_KEY_PREFIX => isset($userCertificateXPublicKey) ?
                        $this->encodeValue($userCertificateXPublicKey) : null,
                    self::PRIVATE_KEY_PREFIX => isset($userCertificateXPrivateKey) ?
                        $this->encodeValue($userCertificateXPrivateKey) : null,
                ],
            ],
            self::BANK_PREFIX => [
                self::CERTIFICATE_PREFIX_E => [
                    self::CERTIFICATE_PREFIX => isset($bankCertificateEB64) ?
                        $this->encodeValue($bankCertificateEB64) : null,
                    self::PUBLIC_KEY_PREFIX => isset($bankCertificateEPublicKey) ?
                        $this->encodeValue($bankCertificateEPublicKey) : null,
                    self::PRIVATE_KEY_PREFIX => isset($bankCertificateEPrivateKey) ?
                        $this->encodeValue($bankCertificateEPrivateKey) : null,
                ],
                self::CERTIFICATE_PREFIX_X => [
                    self::CERTIFICATE_PREFIX => isset($bankCertificateXB64) ?
                        $this->encodeValue($bankCertificateXB64) : null,
                    self::PUBLIC_KEY_PREFIX => isset($bankCertificateXPublicKey) ?
                        $this->encodeValue($bankCertificateXPublicKey) : null,
                    self::PRIVATE_KEY_PREFIX => isset($bankCertificateXPrivateKey) ?
                        $this->encodeValue($bankCertificateXPrivateKey) : null,
                ],
            ],
        ];
    }

    private function encodeValue(string $value): string
    {
        return base64_encode($value);
    }

    private function decodeValue(string $value): string
    {
        return base64_decode($value);
    }
}

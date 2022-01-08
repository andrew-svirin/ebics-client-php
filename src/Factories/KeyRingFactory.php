<?php

namespace AndrewSvirin\Ebics\Factories;

use AndrewSvirin\Ebics\Contracts\SignatureInterface;
use AndrewSvirin\Ebics\Models\KeyRing;

/**
 * Class KeyRingFactory represents producers for the @see KeyRing.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class KeyRingFactory
{
    /**
     * @var SignatureFactory
     */
    private $signatureFactory;

    public function __construct()
    {
        $this->signatureFactory = new SignatureFactory();
    }

    /**
     * @param array $data
     *
     * @return KeyRing
     */
    public function createKeyRingFromData(array $data): KeyRing
    {
        $keyRing = new KeyRing();

        $keyRing->setUserSignatureA(
            $this->buildKeyRingFromDataForTypeKeyRing($data, KeyRing::USER_PREFIX, KeyRing::SIGNATURE_PREFIX_A)
        );

        $keyRing->setUserSignatureE(
            $this->buildKeyRingFromDataForTypeKeyRing($data, KeyRing::USER_PREFIX, KeyRing::SIGNATURE_PREFIX_E)
        );

        $keyRing->setUserSignatureX(
            $this->buildKeyRingFromDataForTypeKeyRing($data, KeyRing::USER_PREFIX, KeyRing::SIGNATURE_PREFIX_X)
        );

        $keyRing->setBankSignatureE(
            $this->buildKeyRingFromDataForTypeKeyRing($data, KeyRing::BANK_PREFIX, KeyRing::SIGNATURE_PREFIX_E)
        );

        $keyRing->setBankSignatureX(
            $this->buildKeyRingFromDataForTypeKeyRing($data, KeyRing::BANK_PREFIX, KeyRing::SIGNATURE_PREFIX_X)
        );

        return $keyRing;
    }

    /**
     * @param array $data
     * @param string $typePrefix
     * @param string $signaturePrefix
     *
     * @return SignatureInterface|null
     */
    private function buildKeyRingFromDataForTypeKeyRing(
        array $data,
        string $typePrefix,
        string $signaturePrefix
    ): ?SignatureInterface {
        if (empty($data[$typePrefix][$signaturePrefix][KeyRing::PUBLIC_KEY_PREFIX])) {
            return null;
        }
        $certificateContent =
            $data[$typePrefix][$signaturePrefix][KeyRing::CERTIFICATE_PREFIX];
        $signaturePublicKey =
            $data[$typePrefix][$signaturePrefix][KeyRing::PUBLIC_KEY_PREFIX];
        $signaturePrivateKey =
            $data[$typePrefix][$signaturePrefix][KeyRing::PRIVATE_KEY_PREFIX];

        $signature = $this->signatureFactory->create(
            $signaturePrefix,
            $this->decodeValue($signaturePublicKey),
            !empty($signaturePrivateKey) ? $this->decodeValue($signaturePrivateKey) : null
        );

        if (!empty($certificateContent)) {
            $signature->setCertificateContent($this->decodeValue($certificateContent));
        }

        return $signature;
    }

    /**
     * @param KeyRing $keyRing
     *
     * @return array
     */
    public function buildDataFromKeyRing(KeyRing $keyRing): array
    {
        if (null !== $keyRing->getUserSignatureA()) {
            $userSignatureAB64 = $keyRing->getUserSignatureA()->getCertificateContent();
            $userSignatureAPublicKey = $keyRing->getUserSignatureA()->getPublicKey();
            $userSignatureAPrivateKey = $keyRing->getUserSignatureA()->getPrivateKey();
        }
        if (null !== $keyRing->getUserSignatureE()) {
            $userSignatureEB64 = $keyRing->getUserSignatureE()->getCertificateContent();
            $userSignatureEPublicKey = $keyRing->getUserSignatureE()->getPublicKey();
            $userSignatureEPrivateKey = $keyRing->getUserSignatureE()->getPrivateKey();
        }
        if (null !== $keyRing->getUserSignatureX()) {
            $userSignatureXB64 = $keyRing->getUserSignatureX()->getCertificateContent();
            $userSignatureXPublicKey = $keyRing->getUserSignatureX()->getPublicKey();
            $userSignatureXPrivateKey = $keyRing->getUserSignatureX()->getPrivateKey();
        }
        if (null !== $keyRing->getBankSignatureE()) {
            $bankSignatureEB64 = $keyRing->getBankSignatureE()->getCertificateContent();
            $bankSignatureEPublicKey = $keyRing->getBankSignatureE()->getPublicKey();
            $bankSignatureEPrivateKey = $keyRing->getBankSignatureE()->getPrivateKey();
        }
        if (null !== $keyRing->getBankSignatureX()) {
            $bankSignatureXB64 = $keyRing->getBankSignatureX()->getCertificateContent();
            $bankSignatureXPublicKey = $keyRing->getBankSignatureX()->getPublicKey();
            $bankSignatureXPrivateKey = $keyRing->getBankSignatureX()->getPrivateKey();
        }

        return [
            KeyRing::USER_PREFIX => [
                KeyRing::SIGNATURE_PREFIX_A => [
                    KeyRing::CERTIFICATE_PREFIX => isset($userSignatureAB64) ?
                        $this->encodeValue($userSignatureAB64) : null,
                    KeyRing::PUBLIC_KEY_PREFIX => isset($userSignatureAPublicKey) ?
                        $this->encodeValue($userSignatureAPublicKey) : null,
                    KeyRing::PRIVATE_KEY_PREFIX => isset($userSignatureAPrivateKey) ?
                        $this->encodeValue($userSignatureAPrivateKey) : null,
                ],
                KeyRing::SIGNATURE_PREFIX_E => [
                    KeyRing::CERTIFICATE_PREFIX => isset($userSignatureEB64) ?
                        $this->encodeValue($userSignatureEB64) : null,
                    KeyRing::PUBLIC_KEY_PREFIX => isset($userSignatureEPublicKey) ?
                        $this->encodeValue($userSignatureEPublicKey) : null,
                    KeyRing::PRIVATE_KEY_PREFIX => isset($userSignatureEPrivateKey) ?
                        $this->encodeValue($userSignatureEPrivateKey) : null,
                ],
                KeyRing::SIGNATURE_PREFIX_X => [
                    KeyRing::CERTIFICATE_PREFIX => isset($userSignatureXB64) ?
                        $this->encodeValue($userSignatureXB64) : null,
                    KeyRing::PUBLIC_KEY_PREFIX => isset($userSignatureXPublicKey) ?
                        $this->encodeValue($userSignatureXPublicKey) : null,
                    KeyRing::PRIVATE_KEY_PREFIX => isset($userSignatureXPrivateKey) ?
                        $this->encodeValue($userSignatureXPrivateKey) : null,
                ],
            ],
            KeyRing::BANK_PREFIX => [
                KeyRing::SIGNATURE_PREFIX_E => [
                    KeyRing::CERTIFICATE_PREFIX => isset($bankSignatureEB64) ?
                        $this->encodeValue($bankSignatureEB64) : null,
                    KeyRing::PUBLIC_KEY_PREFIX => isset($bankSignatureEPublicKey) ?
                        $this->encodeValue($bankSignatureEPublicKey) : null,
                    KeyRing::PRIVATE_KEY_PREFIX => isset($bankSignatureEPrivateKey) ?
                        $this->encodeValue($bankSignatureEPrivateKey) : null,
                ],
                KeyRing::SIGNATURE_PREFIX_X => [
                    KeyRing::CERTIFICATE_PREFIX => isset($bankSignatureXB64) ?
                        $this->encodeValue($bankSignatureXB64) : null,
                    KeyRing::PUBLIC_KEY_PREFIX => isset($bankSignatureXPublicKey) ?
                        $this->encodeValue($bankSignatureXPublicKey) : null,
                    KeyRing::PRIVATE_KEY_PREFIX => isset($bankSignatureXPrivateKey) ?
                        $this->encodeValue($bankSignatureXPrivateKey) : null,
                ],
            ],
        ];
    }

    /**
     * @param string $value
     *
     * @return string
     */
    private function encodeValue(string $value): string
    {
        return base64_encode($value);
    }

    /**
     * @param string $value
     *
     * @return string
     */
    private function decodeValue(string $value): string
    {
        return base64_decode($value);
    }
}

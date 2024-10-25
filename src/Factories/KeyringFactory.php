<?php

namespace AndrewSvirin\Ebics\Factories;

use AndrewSvirin\Ebics\Contracts\SignatureInterface;
use AndrewSvirin\Ebics\Models\Keyring;

/**
 * Class KeyringFactory represents producers for the @see Keyring
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class KeyringFactory
{
    private SignatureFactory $signatureFactory;

    public function __construct()
    {
        $this->signatureFactory = new SignatureFactory();
    }

    /**
     * @param array $data
     *
     * @return Keyring
     */
    public function createKeyringFromData(array $data): Keyring
    {
        $keyring = new Keyring($data[Keyring::VERSION_PREFIX]);

        $keyring->setUserSignatureA(
            $this->buildKeyringFromDataForTypeKeyring($data, Keyring::USER_PREFIX, Keyring::SIGNATURE_PREFIX_A)
        );

        $keyring->setUserSignatureE(
            $this->buildKeyringFromDataForTypeKeyring($data, Keyring::USER_PREFIX, Keyring::SIGNATURE_PREFIX_E)
        );

        $keyring->setUserSignatureX(
            $this->buildKeyringFromDataForTypeKeyring($data, Keyring::USER_PREFIX, Keyring::SIGNATURE_PREFIX_X)
        );

        $keyring->setBankSignatureE(
            $this->buildKeyringFromDataForTypeKeyring($data, Keyring::BANK_PREFIX, Keyring::SIGNATURE_PREFIX_E)
        );

        $keyring->setBankSignatureX(
            $this->buildKeyringFromDataForTypeKeyring($data, Keyring::BANK_PREFIX, Keyring::SIGNATURE_PREFIX_X)
        );

        return $keyring;
    }

    /**
     * @param array $data
     * @param string $typePrefix
     * @param string $signaturePrefix
     *
     * @return SignatureInterface|null
     */
    private function buildKeyringFromDataForTypeKeyring(
        array $data,
        string $typePrefix,
        string $signaturePrefix
    ): ?SignatureInterface {
        if (empty($data[$typePrefix][$signaturePrefix][Keyring::PUBLIC_KEY_PREFIX])) {
            return null;
        }
        $certificateContent
            = $data[$typePrefix][$signaturePrefix][Keyring::CERTIFICATE_PREFIX];
        $signaturePublicKey
            = $data[$typePrefix][$signaturePrefix][Keyring::PUBLIC_KEY_PREFIX];
        $signaturePrivateKey
            = $data[$typePrefix][$signaturePrefix][Keyring::PRIVATE_KEY_PREFIX];

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
     * @param Keyring $keyring
     *
     * @return array
     */
    public function buildDataFromKeyring(Keyring $keyring): array
    {
        if (null !== $keyring->getUserSignatureA()) {
            $userSignatureAB64 = $keyring->getUserSignatureA()->getCertificateContent();
            $userSignatureAPublicKey = $keyring->getUserSignatureA()->getPublicKey();
            $userSignatureAPrivateKey = $keyring->getUserSignatureA()->getPrivateKey();
        }
        if (null !== $keyring->getUserSignatureE()) {
            $userSignatureEB64 = $keyring->getUserSignatureE()->getCertificateContent();
            $userSignatureEPublicKey = $keyring->getUserSignatureE()->getPublicKey();
            $userSignatureEPrivateKey = $keyring->getUserSignatureE()->getPrivateKey();
        }
        if (null !== $keyring->getUserSignatureX()) {
            $userSignatureXB64 = $keyring->getUserSignatureX()->getCertificateContent();
            $userSignatureXPublicKey = $keyring->getUserSignatureX()->getPublicKey();
            $userSignatureXPrivateKey = $keyring->getUserSignatureX()->getPrivateKey();
        }
        if (null !== $keyring->getBankSignatureE()) {
            $bankSignatureEB64 = $keyring->getBankSignatureE()->getCertificateContent();
            $bankSignatureEPublicKey = $keyring->getBankSignatureE()->getPublicKey();
            $bankSignatureEPrivateKey = $keyring->getBankSignatureE()->getPrivateKey();
        }
        if (null !== $keyring->getBankSignatureX()) {
            $bankSignatureXB64 = $keyring->getBankSignatureX()->getCertificateContent();
            $bankSignatureXPublicKey = $keyring->getBankSignatureX()->getPublicKey();
            $bankSignatureXPrivateKey = $keyring->getBankSignatureX()->getPrivateKey();
        }

        return [
            Keyring::VERSION_PREFIX => $keyring->getVersion(),
            Keyring::USER_PREFIX => [
                Keyring::SIGNATURE_PREFIX_A => [
                    Keyring::CERTIFICATE_PREFIX => isset($userSignatureAB64) ?
                        $this->encodeValue($userSignatureAB64) : null,
                    Keyring::PUBLIC_KEY_PREFIX => isset($userSignatureAPublicKey) ?
                        $this->encodeValue($userSignatureAPublicKey) : null,
                    Keyring::PRIVATE_KEY_PREFIX => isset($userSignatureAPrivateKey) ?
                        $this->encodeValue($userSignatureAPrivateKey) : null,
                ],
                Keyring::SIGNATURE_PREFIX_E => [
                    Keyring::CERTIFICATE_PREFIX => isset($userSignatureEB64) ?
                        $this->encodeValue($userSignatureEB64) : null,
                    Keyring::PUBLIC_KEY_PREFIX => isset($userSignatureEPublicKey) ?
                        $this->encodeValue($userSignatureEPublicKey) : null,
                    Keyring::PRIVATE_KEY_PREFIX => isset($userSignatureEPrivateKey) ?
                        $this->encodeValue($userSignatureEPrivateKey) : null,
                ],
                Keyring::SIGNATURE_PREFIX_X => [
                    Keyring::CERTIFICATE_PREFIX => isset($userSignatureXB64) ?
                        $this->encodeValue($userSignatureXB64) : null,
                    Keyring::PUBLIC_KEY_PREFIX => isset($userSignatureXPublicKey) ?
                        $this->encodeValue($userSignatureXPublicKey) : null,
                    Keyring::PRIVATE_KEY_PREFIX => isset($userSignatureXPrivateKey) ?
                        $this->encodeValue($userSignatureXPrivateKey) : null,
                ],
            ],
            Keyring::BANK_PREFIX => [
                Keyring::SIGNATURE_PREFIX_E => [
                    Keyring::CERTIFICATE_PREFIX => isset($bankSignatureEB64) ?
                        $this->encodeValue($bankSignatureEB64) : null,
                    Keyring::PUBLIC_KEY_PREFIX => isset($bankSignatureEPublicKey) ?
                        $this->encodeValue($bankSignatureEPublicKey) : null,
                    Keyring::PRIVATE_KEY_PREFIX => isset($bankSignatureEPrivateKey) ?
                        $this->encodeValue($bankSignatureEPrivateKey) : null,
                ],
                Keyring::SIGNATURE_PREFIX_X => [
                    Keyring::CERTIFICATE_PREFIX => isset($bankSignatureXB64) ?
                        $this->encodeValue($bankSignatureXB64) : null,
                    Keyring::PUBLIC_KEY_PREFIX => isset($bankSignatureXPublicKey) ?
                        $this->encodeValue($bankSignatureXPublicKey) : null,
                    Keyring::PRIVATE_KEY_PREFIX => isset($bankSignatureXPrivateKey) ?
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

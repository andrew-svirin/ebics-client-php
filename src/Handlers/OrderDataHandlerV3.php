<?php

namespace AndrewSvirin\Ebics\Handlers;

use AndrewSvirin\Ebics\Contracts\SignatureInterface;
use AndrewSvirin\Ebics\Handlers\Traits\H005Trait;
use AndrewSvirin\Ebics\Models\Crypt\X509;
use AndrewSvirin\Ebics\Models\CustomerHIA;
use AndrewSvirin\Ebics\Models\CustomerINI;
use AndrewSvirin\Ebics\Models\Document;
use AndrewSvirin\Ebics\Services\DOMHelper;
use DateTimeInterface;
use DOMElement;
use RuntimeException;

/**
 * Ebics 3.0 OrderDataHandler.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @internal
 */
final class OrderDataHandlerV3 extends OrderDataHandler
{
    use H005Trait;

    protected function createSignaturePubKeyOrderData(CustomerINI $xml): DOMElement
    {
        return $xml->createElementNS(
            'http://www.ebics.org/S002',
            'SignaturePubKeyOrderData'
        );
    }

    protected function handleINISignaturePubKey(
        DOMElement $xmlSignaturePubKeyInfo,
        CustomerINI $xml,
        SignatureInterface $certificateA,
        DateTimeInterface $dateTime
    ): void {
        // Is not need for V3.
    }

    protected function handleHIAAuthenticationPubKey(
        DOMElement $xmlAuthenticationPubKeyInfo,
        CustomerHIA $xml,
        SignatureInterface $certificateX,
        DateTimeInterface $dateTime
    ): void {
        // Is not need for V3.
    }

    protected function handleHIAEncryptionPubKey(
        DOMElement $xmlEncryptionPubKeyInfo,
        CustomerHIA $xml,
        SignatureInterface $certificateE,
        DateTimeInterface $dateTime
    ): void {
        // Is not need for V3.
    }

    public function retrieveAuthenticationSignature(Document $document): SignatureInterface
    {
        $h00x = $this->getH00XVersion();
        $xpath = $this->prepareH00XXPath($document);

        $x509Certificate = $xpath->query("//$h00x:AuthenticationPubKeyInfo/ds:X509Data/ds:X509Certificate");
        $x509CertificateValue = DOMHelper::safeItemValueOrNull($x509Certificate);
        $x509CertificateValueDe = base64_decode($x509CertificateValue);

        if (null === $x509CertificateValue) {
            throw new RuntimeException('Version 3.0 is not supported for not certified banks yet.');
        }

        $certificateContent
            = "-----BEGIN CERTIFICATE-----\n".
            chunk_split($x509CertificateValue, 64).
            "-----END CERTIFICATE-----\n";

        $x509 = new X509();
        $x509->loadX509($x509CertificateValueDe);

        $publicKey = $x509->getPublicKey();

        $signature = $this->signatureFactory->createSignatureXFromDetails(
            $publicKey->getModulus(),
            $publicKey->getExponent()
        );

        $signature->setCertificateContent($certificateContent);

        return $signature;
    }

    public function retrieveEncryptionSignature(Document $document): SignatureInterface
    {
        $h00x = $this->getH00XVersion();
        $xpath = $this->prepareH00XXPath($document);

        $x509Certificate = $xpath->query("//$h00x:EncryptionPubKeyInfo/ds:X509Data/ds:X509Certificate");
        $x509CertificateValue = DOMHelper::safeItemValueOrNull($x509Certificate);
        $x509CertificateValueDe = base64_decode($x509CertificateValue);

        if (null === $x509CertificateValue) {
            throw new RuntimeException('Version 3.0 is not supported for not certified banks yet.');
        }

        $certificateContent
            = "-----BEGIN CERTIFICATE-----\n".
            chunk_split($x509CertificateValue, 64).
            "-----END CERTIFICATE-----\n";

        $x509 = new X509();
        $x509->loadX509($x509CertificateValueDe);

        $publicKey = $x509->getPublicKey();

        $signature = $this->signatureFactory->createSignatureEFromDetails(
            $publicKey->getModulus(),
            $publicKey->getExponent()
        );

        $signature->setCertificateContent($certificateContent);

        return $signature;
    }
}

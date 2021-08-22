<?php

namespace AndrewSvirin\Ebics\Handlers;

use AndrewSvirin\Ebics\Contracts\SignatureInterface;
use AndrewSvirin\Ebics\Handlers\Traits\XPathTrait;
use AndrewSvirin\Ebics\Models\Crypt\X509;
use AndrewSvirin\Ebics\Models\CustomerHIA;
use AndrewSvirin\Ebics\Models\CustomerINI;
use AndrewSvirin\Ebics\Models\OrderData;
use AndrewSvirin\Ebics\Services\DOMHelper;
use DateTimeInterface;
use DOMElement;

/**
 * Ebics 3.0 OrderDataHandler.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @internal
 */
class OrderDataHandlerV3 extends OrderDataHandler
{
    use XPathTrait;

    protected function createSignaturePubKeyOrderData(CustomerINI $xml): DOMElement
    {
        return $xml->createElementNS(
            'http://www.ebics.org/S002',
            'SignaturePubKeyOrderData'
        );
    }

    protected function createHIARequestOrderData(CustomerHIA $xml): DOMElement
    {
        return $xml->createElementNS(
            'urn:org:ebics:H005',
            'HIARequestOrderData'
        );
    }

    protected function handleINISignaturePubKey(
        DOMElement $xmlSignaturePubKeyInfo,
        CustomerINI $xml,
        SignatureInterface $certificateA,
        DateTimeInterface $dateTime
    ) {
        // Is not need for V3.
    }

    protected function handleHIAAuthenticationPubKey(
        DOMElement $xmlAuthenticationPubKeyInfo,
        CustomerHIA $xml,
        SignatureInterface $certificateX,
        DateTimeInterface $dateTime
    ) {
        // Is not need for V3.
    }

    protected function handleHIAEncryptionPubKey(
        DOMElement $xmlEncryptionPubKeyInfo,
        CustomerHIA $xml,
        SignatureInterface $certificateE,
        DateTimeInterface $dateTime
    ) {
        // Is not need for V3.
    }

    public function retrieveAuthenticationSignature(OrderData $orderData): SignatureInterface
    {
        $xpath = $this->prepareH005XPath($orderData);

        $x509Certificate = $xpath->query('//H005:AuthenticationPubKeyInfo/ds:X509Data/ds:X509Certificate');
        $x509CertificateValue = DOMHelper::safeItemValueOrNull($x509Certificate);

        if (null === $x509CertificateValue) {
            // TODO: Require to check with DE servers.
            throw new \RuntimeException('Version 3.0 is not supported for not certified banks yet.');
        }

        $x509 = new X509();
        $cert = $x509->loadX509($x509CertificateValue);
        $certificateContent = $x509->saveX509($cert);
        if (false === $certificateContent) {
            $certificateContent = null;
        }

        $certificate = $this->certificateFactory->createCertificateXFromDetails(
            $x509->getPublicKey()->getModulus()->getValue(),
            $x509->getPublicKey()->getExponent()->getValue()
        );

        $certificate->setCertificateContent($certificateContent ?? null);

        return $certificate;
    }

    public function retrieveEncryptionSignature(OrderData $orderData): SignatureInterface
    {
        $xpath = $this->prepareH005XPath($orderData);

        $x509Certificate = $xpath->query('//H005:EncryptionPubKeyInfo/ds:X509Data/ds:X509Certificate');
        $x509CertificateValue = DOMHelper::safeItemValueOrNull($x509Certificate);

        if (null === $x509CertificateValue) {
            // TODO: Require to check with DE servers.
            throw new \RuntimeException('Version 3.0 is not supported for not certified banks yet.');
        }

        $x509 = new X509();
        $cert = $x509->loadX509($x509CertificateValue);
        $certificateContent = $x509->saveX509($cert);
        if (false === $certificateContent) {
            $certificateContent = null;
        }

        $certificate = $this->certificateFactory->createCertificateEFromDetails(
            $x509->getPublicKey()->getModulus()->getValue(),
            $x509->getPublicKey()->getExponent()->getValue()
        );

        $certificate->setCertificateContent($certificateContent ?? null);

        return $certificate;
    }
}

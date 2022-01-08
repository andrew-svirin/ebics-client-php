<?php

namespace AndrewSvirin\Ebics\Handlers;

use AndrewSvirin\Ebics\Contracts\SignatureInterface;
use AndrewSvirin\Ebics\Handlers\Traits\XPathTrait;
use AndrewSvirin\Ebics\Models\CustomerHIA;
use AndrewSvirin\Ebics\Models\CustomerINI;
use AndrewSvirin\Ebics\Models\Document;
use AndrewSvirin\Ebics\Services\DOMHelper;
use DateTimeInterface;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;

/**
 * Ebics 2.5 OrderDataHandler.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @internal
 */
final class OrderDataHandlerV2 extends OrderDataHandler
{
    use XPathTrait;

    protected function createSignaturePubKeyOrderData(CustomerINI $xml): DOMElement
    {
        return $xml->createElementNS(
            'http://www.ebics.org/S001',
            'SignaturePubKeyOrderData'
        );
    }

    protected function createHIARequestOrderData(CustomerHIA $xml): DOMElement
    {
        return $xml->createElementNS(
            'urn:org:ebics:H004',
            'HIARequestOrderData'
        );
    }

    protected function handleINISignaturePubKey(
        DOMElement $xmlSignaturePubKeyInfo,
        CustomerINI $xml,
        SignatureInterface $certificateA,
        DateTimeInterface $dateTime
    ): void {
        $this->handlePubKeyValue($xmlSignaturePubKeyInfo, $xml, $certificateA, $dateTime);
    }

    protected function handleHIAAuthenticationPubKey(
        DOMElement $xmlAuthenticationPubKeyInfo,
        CustomerHIA $xml,
        SignatureInterface $certificateX,
        DateTimeInterface $dateTime
    ): void {
        $this->handlePubKeyValue($xmlAuthenticationPubKeyInfo, $xml, $certificateX, $dateTime);
    }

    protected function handleHIAEncryptionPubKey(
        DOMElement $xmlEncryptionPubKeyInfo,
        CustomerHIA $xml,
        SignatureInterface $certificateE,
        DateTimeInterface $dateTime
    ): void {
        $this->handlePubKeyValue($xmlEncryptionPubKeyInfo, $xml, $certificateE, $dateTime);
    }

    /**
     * Add PubKeyValue to PublicKeyInfo XML Node.
     *
     * @param DOMNode $xmlPublicKeyInfo
     * @param DOMDocument $xml
     * @param SignatureInterface $certificate
     * @param DateTimeInterface $dateTime
     */
    private function handlePubKeyValue(
        DOMNode $xmlPublicKeyInfo,
        DOMDocument $xml,
        SignatureInterface $certificate,
        DateTimeInterface $dateTime
    ): void {
        $publicKeyDetails = $this->cryptService->getPublicKeyDetails($certificate->getPublicKey());

        // Add PubKeyValue to Signature.
        $xmlPubKeyValue = $xml->createElement('PubKeyValue');
        $xmlPublicKeyInfo->appendChild($xmlPubKeyValue);

        // Add ds:RSAKeyValue to PubKeyValue.
        $xmlRSAKeyValue = $xml->createElement('ds:RSAKeyValue');
        $xmlPubKeyValue->appendChild($xmlRSAKeyValue);

        // Add ds:Modulus to ds:RSAKeyValue.
        $xmlModulus = $xml->createElement('ds:Modulus');
        $xmlModulus->nodeValue = base64_encode($publicKeyDetails['m']);
        $xmlRSAKeyValue->appendChild($xmlModulus);

        // Add ds:Exponent to ds:RSAKeyValue.
        $xmlExponent = $xml->createElement('ds:Exponent');
        $xmlExponent->nodeValue = base64_encode($publicKeyDetails['e']);
        $xmlRSAKeyValue->appendChild($xmlExponent);

        // Add TimeStamp to PubKeyValue.
        $xmlTimeStamp = $xml->createElement('TimeStamp');
        $xmlTimeStamp->nodeValue = $dateTime->format('Y-m-d\TH:i:s\Z');
        $xmlPubKeyValue->appendChild($xmlTimeStamp);
    }

    public function retrieveAuthenticationSignature(Document $document): SignatureInterface
    {
        $xpath = $this->prepareH004XPath($document);

        $modulus = $xpath->query('//H004:AuthenticationPubKeyInfo/H004:PubKeyValue/ds:RSAKeyValue/ds:Modulus');
        $modulusValue = DOMHelper::safeItemValue($modulus);
        $modulusValueDe = base64_decode($modulusValue);
        $exponent = $xpath->query('//H004:AuthenticationPubKeyInfo/H004:PubKeyValue/ds:RSAKeyValue/ds:Exponent');
        $exponentValue = DOMHelper::safeItemValue($exponent);
        $exponentValueDe = base64_decode($exponentValue);

        $certificate = $this->certificateFactory->createCertificateXFromDetails(
            $modulusValueDe,
            $exponentValueDe
        );

        $x509Certificate = $xpath->query('//H004:AuthenticationPubKeyInfo/ds:X509Data/ds:X509Certificate');
        if ($x509Certificate instanceof DOMNodeList && 0 !== $x509Certificate->length) {
            $x509CertificateValue = DOMHelper::safeItemValue($x509Certificate);
            $x509CertificateValueDe = base64_decode($x509CertificateValue);
            $certificate->setCertificateContent($x509CertificateValueDe);
        }

        return $certificate;
    }

    public function retrieveEncryptionSignature(Document $document): SignatureInterface
    {
        $xpath = $this->prepareH004XPath($document);

        $modulus = $xpath->query('//H004:EncryptionPubKeyInfo/H004:PubKeyValue/ds:RSAKeyValue/ds:Modulus');
        $modulusValue = DOMHelper::safeItemValue($modulus);
        $modulusValueDe = base64_decode($modulusValue);
        $exponent = $xpath->query('//H004:EncryptionPubKeyInfo/H004:PubKeyValue/ds:RSAKeyValue/ds:Exponent');
        $exponentValue = DOMHelper::safeItemValue($exponent);
        $exponentValueDe = base64_decode($exponentValue);

        $certificate = $this->certificateFactory->createCertificateEFromDetails(
            $modulusValueDe,
            $exponentValueDe
        );

        $x509Certificate = $xpath->query('//H004:EncryptionPubKeyInfo/ds:X509Data/ds:X509Certificate');
        if ($x509Certificate instanceof DOMNodeList && 0 !== $x509Certificate->length) {
            $x509CertificateValue = DOMHelper::safeItemValue($x509Certificate);
            $x509CertificateValueDe = base64_decode($x509CertificateValue);
            $certificate->setCertificateContent($x509CertificateValueDe);
        }

        return $certificate;
    }
}

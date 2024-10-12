<?php

namespace AndrewSvirin\Ebics\Handlers;

use AndrewSvirin\Ebics\Contracts\SignatureInterface;
use AndrewSvirin\Ebics\Handlers\Traits\H00XTrait;
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
 * Ebics 2.x OrderDataHandler.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @internal
 */
abstract class OrderDataHandlerV2 extends OrderDataHandler
{
    use H00XTrait;

    protected function createSignaturePubKeyOrderData(CustomerINI $xml): DOMElement
    {
        return $xml->createElementNS(
            'http://www.ebics.org/S001',
            'SignaturePubKeyOrderData'
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
        $h00x = $this->getH00XVersion();
        $xpath = $this->prepareH00XXPath($document);

        $modulus = $xpath->query("//$h00x:AuthenticationPubKeyInfo/$h00x:PubKeyValue/ds:RSAKeyValue/ds:Modulus");
        $modulusValue = DOMHelper::safeItemValue($modulus);
        $modulusValueDe = base64_decode($modulusValue);
        $exponent = $xpath->query("//$h00x:AuthenticationPubKeyInfo/$h00x:PubKeyValue/ds:RSAKeyValue/ds:Exponent");
        $exponentValue = DOMHelper::safeItemValue($exponent);
        $exponentValueDe = base64_decode($exponentValue);

        $signature = $this->signatureFactory->createSignatureXFromDetails(
            $this->bigIntegerFactory->create($modulusValueDe, 256),
            $this->bigIntegerFactory->create($exponentValueDe, 256)
        );

        $x509Certificate = $xpath->query("//$h00x:AuthenticationPubKeyInfo/ds:X509Data/ds:X509Certificate");
        if ($x509Certificate instanceof DOMNodeList && 0 !== $x509Certificate->length) {
            $x509CertificateValue = DOMHelper::safeItemValue($x509Certificate);
            $x509CertificateValueDe = base64_decode($x509CertificateValue);
            $signature->setCertificateContent($x509CertificateValueDe);
        }

        return $signature;
    }

    public function retrieveEncryptionSignature(Document $document): SignatureInterface
    {
        $h00x = $this->getH00XVersion();
        $xpath = $this->prepareH00XXPath($document);

        $modulus = $xpath->query("//$h00x:EncryptionPubKeyInfo/$h00x:PubKeyValue/ds:RSAKeyValue/ds:Modulus");
        $modulusValue = DOMHelper::safeItemValue($modulus);
        $modulusValueDe = base64_decode($modulusValue);
        $exponent = $xpath->query("//$h00x:EncryptionPubKeyInfo/$h00x:PubKeyValue/ds:RSAKeyValue/ds:Exponent");
        $exponentValue = DOMHelper::safeItemValue($exponent);
        $exponentValueDe = base64_decode($exponentValue);

        $signature = $this->signatureFactory->createSignatureEFromDetails(
            $this->bigIntegerFactory->create($modulusValueDe, 256),
            $this->bigIntegerFactory->create($exponentValueDe, 256)
        );

        $x509Certificate = $xpath->query("//$h00x:EncryptionPubKeyInfo/ds:X509Data/ds:X509Certificate");
        if ($x509Certificate instanceof DOMNodeList && 0 !== $x509Certificate->length) {
            $x509CertificateValue = DOMHelper::safeItemValue($x509Certificate);
            $x509CertificateValueDe = base64_decode($x509CertificateValue);
            $signature->setCertificateContent($x509CertificateValueDe);
        }

        return $signature;
    }
}

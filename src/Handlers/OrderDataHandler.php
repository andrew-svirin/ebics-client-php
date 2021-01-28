<?php

namespace AndrewSvirin\Ebics\Handlers;

use AndrewSvirin\Ebics\Contracts\SignatureInterface;
use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Factories\CertificateX509Factory;
use AndrewSvirin\Ebics\Factories\SignatureFactory;
use AndrewSvirin\Ebics\Handlers\Traits\XPathTrait;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Models\OrderData;
use AndrewSvirin\Ebics\Models\User;
use AndrewSvirin\Ebics\Services\CryptService;
use AndrewSvirin\Ebics\Services\DOMHelper;
use DateTimeInterface;
use DOMDocument;
use DOMNode;
use DOMNodeList;

/**
 * Class OrderDataHandler manages OrderData DOM elements.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class OrderDataHandler
{
    use XPathTrait;

    /**
     * @var Bank
     */
    private $bank;

    /**
     * @var User
     */
    private $user;

    /**
     * @var KeyRing
     */
    private $keyRing;

    /**
     * @var CryptService
     */
    private $cryptService;

    /**
     * @var SignatureFactory
     */
    private $certificateFactory;

    /**
     * @var CertificateX509Factory
     */
    private $certificateX509Factory;

    public function __construct(Bank $bank, User $user, KeyRing $keyRing)
    {
        $this->bank = $bank;
        $this->user = $user;
        $this->keyRing = $keyRing;
        $this->cryptService = new CryptService();
        $this->certificateFactory = new SignatureFactory();
        $this->certificateX509Factory = new CertificateX509Factory();
    }

    /**
     * Adds OrderData DOM elements to XML DOM for INI request.
     *
     * @param DOMDocument $xml
     * @param SignatureInterface $certificateA
     * @param DateTimeInterface $dateTime
     *
     * @throws EbicsException
     */
    public function handleINI(DOMDocument $xml, SignatureInterface $certificateA, DateTimeInterface $dateTime): void
    {
        // Add SignaturePubKeyOrderData to root.
        $xmlSignaturePubKeyOrderData = $xml->createElementNS(
            'http://www.ebics.org/S001',
            'SignaturePubKeyOrderData'
        );
        $xmlSignaturePubKeyOrderData->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:ds',
            'http://www.w3.org/2000/09/xmldsig#'
        );
        $xml->appendChild($xmlSignaturePubKeyOrderData);

        // Add SignaturePubKeyInfo to SignaturePubKeyOrderData.
        $xmlSignaturePubKeyInfo = $xml->createElement('SignaturePubKeyInfo');
        $xmlSignaturePubKeyOrderData->appendChild($xmlSignaturePubKeyInfo);

        if ($this->bank->isCertified()) {
            $this->handleX509Data($xmlSignaturePubKeyInfo, $xml, $certificateA);
        }
        $this->handlePubKeyValue($xmlSignaturePubKeyInfo, $xml, $certificateA, $dateTime);

        // Add SignatureVersion to SignaturePubKeyInfo.
        $xmlSignatureVersion = $xml->createElement('SignatureVersion');
        $xmlSignatureVersion->nodeValue = $this->keyRing->getUserSignatureAVersion();
        $xmlSignaturePubKeyInfo->appendChild($xmlSignatureVersion);

        // Add PartnerID to SignaturePubKeyOrderData.
        $this->handlePartnerId($xmlSignaturePubKeyOrderData, $xml);

        // Add UserID to SignaturePubKeyOrderData.
        $this->handleUserId($xmlSignaturePubKeyOrderData, $xml);
    }

    /**
     * Adds OrderData DOM elements to XML DOM for HIA request.
     *
     * @param DOMDocument $xml
     * @param SignatureInterface $certificateE
     * @param SignatureInterface $certificateX
     * @param DateTimeInterface $dateTime
     *
     * @throws EbicsException
     */
    public function handleHIA(
        DOMDocument $xml,
        SignatureInterface $certificateE,
        SignatureInterface $certificateX,
        DateTimeInterface $dateTime
    ): void {
        // Add HIARequestOrderData to root.
        $xmlHIARequestOrderData = $xml->createElementNS(
            'urn:org:ebics:H004',
            'HIARequestOrderData'
        );
        $xmlHIARequestOrderData->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:ds',
            'http://www.w3.org/2000/09/xmldsig#'
        );
        $xml->appendChild($xmlHIARequestOrderData);

        // Add AuthenticationPubKeyInfo to HIARequestOrderData.
        $xmlAuthenticationPubKeyInfo = $xml->createElement('AuthenticationPubKeyInfo');
        $xmlHIARequestOrderData->appendChild($xmlAuthenticationPubKeyInfo);

        if ($this->bank->isCertified()) {
            $this->handleX509Data($xmlAuthenticationPubKeyInfo, $xml, $certificateX);
        }
        $this->handlePubKeyValue($xmlAuthenticationPubKeyInfo, $xml, $certificateX, $dateTime);

        // Add AuthenticationVersion to AuthenticationPubKeyInfo.
        $xmlAuthenticationVersion = $xml->createElement('AuthenticationVersion');
        $xmlAuthenticationVersion->nodeValue = $this->keyRing->getUserSignatureXVersion();
        $xmlAuthenticationPubKeyInfo->appendChild($xmlAuthenticationVersion);

        // Add EncryptionPubKeyInfo to HIARequestOrderData.
        $xmlEncryptionPubKeyInfo = $xml->createElement('EncryptionPubKeyInfo');
        $xmlHIARequestOrderData->appendChild($xmlEncryptionPubKeyInfo);

        if ($this->bank->isCertified()) {
            $this->handleX509Data($xmlEncryptionPubKeyInfo, $xml, $certificateE);
        }
        $this->handlePubKeyValue($xmlEncryptionPubKeyInfo, $xml, $certificateE, $dateTime);

        // Add EncryptionVersion to EncryptionPubKeyInfo.
        $xmlEncryptionVersion = $xml->createElement('EncryptionVersion');
        $xmlEncryptionVersion->nodeValue = $this->keyRing->getUserSignatureEVersion();
        $xmlEncryptionPubKeyInfo->appendChild($xmlEncryptionVersion);

        // Add PartnerID to HIARequestOrderData.
        $this->handlePartnerId($xmlHIARequestOrderData, $xml);

        // Add UserID to HIARequestOrderData.
        $this->handleUserId($xmlHIARequestOrderData, $xml);
    }

    /**
     * Add ds:X509Data to PublicKeyInfo XML Node.
     *
     * @param DOMNode $xmlPublicKeyInfo
     * @param DOMDocument $xml
     * @param SignatureInterface $certificate
     *
     * @throws EbicsException
     */
    private function handleX509Data(DOMNode $xmlPublicKeyInfo, DOMDocument $xml, SignatureInterface $certificate): void
    {
        if (!($certificateContent = $certificate->getCertificateContent())) {
            throw new EbicsException('Certificate X509 is empty.');
        }
        $certificateX509 = $this->certificateX509Factory->createFromContent($certificateContent);

        // Add ds:X509Data to Signature.
        $xmlX509Data = $xml->createElement('ds:X509Data');
        $xmlPublicKeyInfo->appendChild($xmlX509Data);

        // Add ds:X509IssuerSerial to ds:X509Data.
        $xmlX509IssuerSerial = $xml->createElement('ds:X509IssuerSerial');
        $xmlX509Data->appendChild($xmlX509IssuerSerial);

        // Add ds:X509IssuerName to ds:X509IssuerSerial.
        $xmlX509IssuerName = $xml->createElement('ds:X509IssuerName');
        $xmlX509IssuerName->nodeValue = $certificateX509->getInsurerName();
        $xmlX509IssuerSerial->appendChild($xmlX509IssuerName);

        // Add ds:X509SerialNumber to ds:X509IssuerSerial.
        $xmlX509SerialNumber = $xml->createElement('ds:X509SerialNumber');
        $xmlX509SerialNumber->nodeValue = $certificateX509->getSerialNumber();
        $xmlX509IssuerSerial->appendChild($xmlX509SerialNumber);

        // Add ds:X509Certificate to ds:X509Data.
        $xmlX509Certificate = $xml->createElement('ds:X509Certificate');
        $xmlX509Certificate->nodeValue = base64_encode($certificate->getCertificateContent());
        $xmlX509Data->appendChild($xmlX509Certificate);
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

    /**
     * Add PartnerID to OrderData XML Node.
     *
     * @param DOMNode $xmlOrderData
     * @param DOMDocument $xml
     */
    private function handlePartnerId(DOMNode $xmlOrderData, DOMDocument $xml): void
    {
        $xmlPartnerID = $xml->createElement('PartnerID');
        $xmlPartnerID->nodeValue = $this->user->getPartnerId();
        $xmlOrderData->appendChild($xmlPartnerID);
    }

    /**
     * Add UserID to OrderData XML Node.
     *
     * @param DOMNode $xmlOrderData
     * @param DOMDocument $xml
     */
    private function handleUserId(DOMNode $xmlOrderData, DOMDocument $xml): void
    {
        $xmlUserID = $xml->createElement('UserID');
        $xmlUserID->nodeValue = $this->user->getUserId();
        $xmlOrderData->appendChild($xmlUserID);
    }

    /**
     * Extract Authentication Certificate from the $orderData.
     *
     * @param OrderData $orderData
     *
     * @return SignatureInterface
     */
    public function retrieveAuthenticationSignature(OrderData $orderData): SignatureInterface
    {
        $xpath = $this->prepareH004XPath($orderData);

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

    /**
     * Extract Encryption Certificate from the $orderData.
     *
     * @param OrderData $orderData
     *
     * @return SignatureInterface
     */
    public function retrieveEncryptionSignature(OrderData $orderData): SignatureInterface
    {
        $xpath = $this->prepareH004XPath($orderData);

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

<?php

namespace AndrewSvirin\Ebics\Handlers;

use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Handlers\Traits\C14NTrait;
use AndrewSvirin\Ebics\Handlers\Traits\XPathTrait;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Models\User;
use AndrewSvirin\Ebics\Models\UserSignature;
use AndrewSvirin\Ebics\Services\CryptService;
use DOMDocument;
use DOMNode;

/**
 * Class AuthSignatureHandler manage body DOM elements.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @internal
 */
final class UserSignatureHandler
{
    use C14NTrait;
    use XPathTrait;

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

    public function __construct(User $user, KeyRing $keyRing)
    {
        $this->user = $user;
        $this->keyRing = $keyRing;
        $this->cryptService = new CryptService();
    }

    /**
     * Add body and children elements to request.
     * Build signature value before added PartnerID and UserID.
     *
     * @param UserSignature $xml
     * @param string $orderData
     *
     * @throws EbicsException
     */
    public function handle(UserSignature $xml, string $orderData): void
    {
        // Add UserSignatureData to root.
        $xmlUserSignatureData = $xml->createElementNS(
            'http://www.ebics.org/S001',
            'UserSignatureData'
        );
        $xmlUserSignatureData->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:xsi',
            'http://www.w3.org/2001/XMLSchema-instance'
        );
        $xmlUserSignatureData->setAttributeNS(
            'http://www.w3.org/2001/XMLSchema-instance',
            'xsi:schemaLocation',
            'http://www.ebics.org/S001 http://www.ebics.org/S001/ebics_signature.xsd'
        );
        $xml->appendChild($xmlUserSignatureData);

        // Add OrderSignatureData to UserSignatureData.
        $xmlOrderSignatureData = $xml->createElement('OrderSignatureData');
        $xmlUserSignatureData->appendChild($xmlOrderSignatureData);

        // Add SignatureVersion to OrderSignatureData.
        $xmlSignatureVersion = $xml->createElement('SignatureVersion');
        $xmlSignatureVersion->nodeValue = $this->keyRing->getUserSignatureAVersion();
        $xmlOrderSignatureData->appendChild($xmlSignatureVersion);

        $signatureMethodAlgorithm = 'sha256';

        // Add SignatureValue to OrderSignatureData.
        $canonicalizedUserSignatureData = $orderData;
        $canonicalizedUserSignatureDataHash = $this->cryptService->calculateHash(
            $canonicalizedUserSignatureData,
            $signatureMethodAlgorithm
        );
        $canonicalizedUserSignatureDataHashSigned = $this->cryptService->encryptSignatureValue(
            $this->keyRing->getUserSignatureA()->getPrivateKey(),
            $this->keyRing->getPassword(),
            $canonicalizedUserSignatureDataHash
        );
        $signatureValueNodeValue = base64_encode($canonicalizedUserSignatureDataHashSigned);

        $xmlSignatureValue = $xml->createElement('SignatureValue');
        $xmlSignatureValue->nodeValue = $signatureValueNodeValue;
        $xmlOrderSignatureData->appendChild($xmlSignatureValue);

        $this->insertAfter($xmlSignatureValue, $xmlSignatureVersion);

        // Add PartnerID to OrderSignatureData.
        $this->handlePartnerId($xmlOrderSignatureData, $xml);

        // Add UserID to OrderSignatureData.
        $this->handleUserId($xmlOrderSignatureData, $xml);
    }

    /**
     * Add PartnerID to OrderData XML Node.
     *
     * @param DOMNode $xmlOrderSignatureData
     * @param DOMDocument $xml
     */
    private function handlePartnerId(DOMNode $xmlOrderSignatureData, DOMDocument $xml): void
    {
        $xmlPartnerID = $xml->createElement('PartnerID');
        $xmlPartnerID->nodeValue = $this->user->getPartnerId();
        $xmlOrderSignatureData->appendChild($xmlPartnerID);
    }

    /**
     * Add UserID to OrderData XML Node.
     *
     * @param DOMNode $xmlOrderSignatureData
     * @param DOMDocument $xml
     */
    private function handleUserId(DOMNode $xmlOrderSignatureData, DOMDocument $xml): void
    {
        $xmlUserID = $xml->createElement('UserID');
        $xmlUserID->nodeValue = $this->user->getUserId();
        $xmlOrderSignatureData->appendChild($xmlUserID);
    }
}

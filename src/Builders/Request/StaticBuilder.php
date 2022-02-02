<?php

namespace AndrewSvirin\Ebics\Builders\Request;

use AndrewSvirin\Ebics\Services\CryptService;
use Closure;
use DateTimeInterface;
use DOMDocument;
use DOMElement;

/**
 * Class StaticBuilder builder for request container.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
abstract class StaticBuilder
{
    const SECURITY_MEDIUM_0000 = '0000';
    const SECURITY_MEDIUM_0100 = '0100';
    const SECURITY_MEDIUM_0200 = '0200';

    /**
     * @var DOMElement
     */
    protected $instance;

    /**
     * @var DOMDocument
     */
    protected $dom;

    /**
     * @var CryptService
     */
    protected $cryptService;

    public function __construct(DOMDocument $dom = null)
    {
        $this->dom = $dom;
        $this->cryptService = new CryptService();
    }

    /**
     * Create body for UnsecuredRequest.
     *
     * @return $this
     */
    public function createInstance(): StaticBuilder
    {
        $this->instance = $this->dom->createElement('static');

        return $this;
    }

    public function addHostId(string $hostId): StaticBuilder
    {
        $xmlHostId = $this->dom->createElement('HostID');
        $xmlHostId->nodeValue = $hostId;
        $this->instance->appendChild($xmlHostId);

        return $this;
    }

    public function addRandomNonce(): StaticBuilder
    {
        $nonce = $this->cryptService->generateNonce();

        $xmlNonce = $this->dom->createElement('Nonce');
        $xmlNonce->nodeValue = $nonce;
        $this->instance->appendChild($xmlNonce);

        return $this;
    }

    public function addTimestamp(DateTimeInterface $dateTime): StaticBuilder
    {
        $xmlTimeStamp = $this->dom->createElement('Timestamp');
        $xmlTimeStamp->nodeValue = $dateTime->format('Y-m-d\TH:i:s\Z');
        $this->instance->appendChild($xmlTimeStamp);

        return $this;
    }

    public function addPartnerId(string $partnerId): StaticBuilder
    {
        $xmlPartnerId = $this->dom->createElement('PartnerID');
        $xmlPartnerId->nodeValue = $partnerId;
        $this->instance->appendChild($xmlPartnerId);

        return $this;
    }

    public function addUserId(string $userId): StaticBuilder
    {
        $xmlUserId = $this->dom->createElement('UserID');
        $xmlUserId->nodeValue = $userId;
        $this->instance->appendChild($xmlUserId);

        return $this;
    }

    public function addProduct(string $product, string $language): StaticBuilder
    {
        $xmlProduct = $this->dom->createElement('Product');
        $xmlProduct->setAttribute('Language', $language);
        $xmlProduct->nodeValue = $product;
        $this->instance->appendChild($xmlProduct);

        return $this;
    }

    abstract public function addOrderDetails(Closure $callable = null): StaticBuilder;

    public function addNumSegments(int $numSegments): StaticBuilder
    {
        $xmlNumSegments = $this->dom->createElement('NumSegments');
        $xmlNumSegments->nodeValue = (string)$numSegments;
        $this->instance->appendChild($xmlNumSegments);

        return $this;
    }

    public function addBankPubKeyDigests(
        string $versionX,
        string $certificateXDigest,
        string $versionE,
        string $certificateEDigest,
        string $algorithm = 'sha256'
    ): StaticBuilder {
        $xmlBankPubKeyDigests = $this->dom->createElement('BankPubKeyDigests');
        $this->instance->appendChild($xmlBankPubKeyDigests);

        $authenticationNodeValue = base64_encode($certificateXDigest);

        $encryptionNodeValue = base64_encode($certificateEDigest);

        // Add Authentication to BankPubKeyDigests.
        $xmlAuthentication = $this->dom->createElement('Authentication');
        $xmlAuthentication->setAttribute('Version', $versionX);
        $xmlAuthentication->setAttribute('Algorithm', sprintf('http://www.w3.org/2001/04/xmlenc#%s', $algorithm));
        $xmlAuthentication->nodeValue = $authenticationNodeValue;
        $xmlBankPubKeyDigests->appendChild($xmlAuthentication);

        // Add Encryption to BankPubKeyDigests.
        $xmlEncryption = $this->dom->createElement('Encryption');
        $xmlEncryption->setAttribute('Version', $versionE);
        $xmlEncryption->setAttribute('Algorithm', sprintf('http://www.w3.org/2001/04/xmlenc#%s', $algorithm));
        $xmlEncryption->nodeValue = $encryptionNodeValue;
        $xmlBankPubKeyDigests->appendChild($xmlEncryption);

        return $this;
    }

    public function addSecurityMedium(string $securityMedium): StaticBuilder
    {
        $xmlSecurityMedium = $this->dom->createElement('SecurityMedium');
        $xmlSecurityMedium->nodeValue = $securityMedium;
        $this->instance->appendChild($xmlSecurityMedium);

        return $this;
    }

    public function addTransactionId(string $transactionId): StaticBuilder
    {
        $xmlTransactionID = $this->dom->createElement('TransactionID');
        $xmlTransactionID->nodeValue = $transactionId;
        $this->instance->appendChild($xmlTransactionID);

        return $this;
    }

    public function getInstance(): DOMElement
    {
        return $this->instance;
    }
}

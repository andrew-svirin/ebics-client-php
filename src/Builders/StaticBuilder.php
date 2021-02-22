<?php

namespace AndrewSvirin\Ebics\Builders;

use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Models\KeyRing;
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
class StaticBuilder
{

    const SECURITY_MEDIUM_0000 = '0000';

    /**
     * @var DOMElement
     */
    private $instance;

    /**
     * @var DOMDocument
     */
    private $dom;

    /**
     * @var CryptService
     */
    private $cryptService;

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

    public function addOrderDetails(Closure $callable = null): StaticBuilder
    {
        $orderDetailsBuilder = new OrderDetailsBuilder($this->dom);
        $this->instance->appendChild($orderDetailsBuilder->createInstance()->getInstance());

        call_user_func($callable, $orderDetailsBuilder);

        return $this;
    }

    public function addBank(KeyRing $keyRing, string $algorithm = 'sha256'): StaticBuilder
    {
        $xmlBankPubKeyDigests = $this->dom->createElement('BankPubKeyDigests');
        $this->instance->appendChild($xmlBankPubKeyDigests);

        if (!($signatureX = $keyRing->getBankSignatureX())) {
            throw new EbicsException('Certificate X is empty.');
        }

        if (!($signatureE = $keyRing->getBankSignatureE())) {
            throw new EbicsException('Certificate E is empty.');
        }

        // Add Authentication to BankPubKeyDigests.
        $xmlAuthentication = $this->dom->createElement('Authentication');
        $xmlAuthentication->setAttribute('Version', $keyRing->getBankSignatureXVersion());
        $xmlAuthentication->setAttribute('Algorithm', sprintf('http://www.w3.org/2001/04/xmlenc#%s', $algorithm));
        $certificateXDigest = $this->cryptService->calculateDigest($signatureX, $algorithm, true);
        $xmlAuthentication->nodeValue = base64_encode($certificateXDigest);
        $xmlBankPubKeyDigests->appendChild($xmlAuthentication);

        // Add Encryption to BankPubKeyDigests.
        $xmlEncryption = $this->dom->createElement('Encryption');
        $xmlEncryption->setAttribute('Version', $keyRing->getBankSignatureEVersion());
        $xmlEncryption->setAttribute('Algorithm', sprintf('http://www.w3.org/2001/04/xmlenc#%s', $algorithm));
        $certificateEDigest = $this->cryptService->calculateDigest($signatureE, $algorithm, true);
        $xmlEncryption->nodeValue = base64_encode($certificateEDigest);
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

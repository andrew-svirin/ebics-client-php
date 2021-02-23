<?php

namespace AndrewSvirin\Ebics\Builders;

use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Services\CryptService;
use DOMDocument;
use DOMElement;

/**
 * Class DataEncryptionInfoBuilder builder for request container.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class DataEncryptionInfoBuilder
{

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
    public function createInstance(): DataEncryptionInfoBuilder
    {
        $this->instance = $this->dom->createElement('DataEncryptionInfo');
        $this->instance->setAttribute('authenticate', 'true');

        return $this;
    }

    /**
     * Uses bank signature.
     *
     * @param KeyRing $keyRing
     * @param string $algorithm
     *
     * @return $this
     * @throws EbicsException
     */
    public function addEncryptionPubKeyDigest(KeyRing $keyRing, string $algorithm = 'sha256'): DataEncryptionInfoBuilder
    {
        if (!($signatureE = $keyRing->getBankSignatureE())) {
            throw new EbicsException('Bank Certificate E is empty.');
        }

        $xmlEncryptionPubKeyDigest = $this->dom->createElement('EncryptionPubKeyDigest');
        $xmlEncryptionPubKeyDigest->setAttribute('Version', $keyRing->getBankSignatureEVersion());
        $xmlEncryptionPubKeyDigest->setAttribute(
            'Algorithm',
            sprintf('http://www.w3.org/2001/04/xmlenc#%s', $algorithm)
        );
        $certificateEDigest = $this->cryptService->calculateDigest($signatureE, $algorithm, true);
        $xmlEncryptionPubKeyDigest->nodeValue = base64_encode($certificateEDigest);
        $this->instance->appendChild($xmlEncryptionPubKeyDigest);

        return $this;
    }

    public function addTransactionKey(string $transactionKey, KeyRing $keyRing): DataEncryptionInfoBuilder
    {
        $transactionKeyEncrypted = $this->cryptService->encryptTransactionKey(
            $keyRing->getBankSignatureE()->getPublicKey(),
            $transactionKey
        );

        $xmlTransactionKey = $this->dom->createElement('TransactionKey');
        $xmlTransactionKey->nodeValue = base64_encode($transactionKeyEncrypted);
        $this->instance->appendChild($xmlTransactionKey);

        return $this;
    }

    public function getInstance(): DOMElement
    {
        return $this->instance;
    }
}

<?php

namespace AndrewSvirin\Ebics\Builders\Request;

use AndrewSvirin\Ebics\Exceptions\SignatureEbicsException;
use AndrewSvirin\Ebics\Models\Keyring;
use AndrewSvirin\Ebics\Services\CryptService;
use DOMDocument;
use DOMElement;

/**
 * Class DataEncryptionInfoBuilder builder for request container.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class DataEncryptionInfoBuilder
{
    private DOMElement $instance;
    private ?DOMDocument $dom;
    private CryptService $cryptService;

    public function __construct(?DOMDocument $dom = null)
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
     * @param Keyring $keyring
     * @param string $algorithm
     *
     * @return $this
     * @throws SignatureEbicsException
     */
    public function addEncryptionPubKeyDigest(Keyring $keyring, string $algorithm = 'sha256'): DataEncryptionInfoBuilder
    {
        if (!($signatureE = $keyring->getBankSignatureE())) {
            throw new SignatureEbicsException('Bank Certificate E is empty.');
        }
        $certificateEDigest = $this->cryptService->calculateDigest($signatureE, $algorithm);
        $encryptionPubKeyDigestNodeValue = base64_encode($certificateEDigest);

        $xmlEncryptionPubKeyDigest = $this->dom->createElement('EncryptionPubKeyDigest');
        $xmlEncryptionPubKeyDigest->setAttribute('Version', $keyring->getBankSignatureEVersion());
        $xmlEncryptionPubKeyDigest->setAttribute(
            'Algorithm',
            sprintf('http://www.w3.org/2001/04/xmlenc#%s', $algorithm)
        );
        $xmlEncryptionPubKeyDigest->nodeValue = $encryptionPubKeyDigestNodeValue;
        $this->instance->appendChild($xmlEncryptionPubKeyDigest);

        return $this;
    }

    public function addTransactionKey(string $transactionKey, Keyring $keyring): DataEncryptionInfoBuilder
    {
        $transactionKeyEncrypted = $this->cryptService->encryptTransactionKey(
            $keyring->getBankSignatureE()->getPublicKey(),
            $transactionKey
        );
        $transactionKeyNodeValue = base64_encode($transactionKeyEncrypted);

        $xmlTransactionKey = $this->dom->createElement('TransactionKey');
        $xmlTransactionKey->nodeValue = $transactionKeyNodeValue;
        $this->instance->appendChild($xmlTransactionKey);

        return $this;
    }

    public function getInstance(): DOMElement
    {
        return $this->instance;
    }
}

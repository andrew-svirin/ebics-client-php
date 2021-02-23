<?php

namespace AndrewSvirin\Ebics\Builders;

use AndrewSvirin\Ebics\Contracts\OrderDataInterface;
use AndrewSvirin\Ebics\Contracts\SignatureDataInterface;
use AndrewSvirin\Ebics\Services\CryptService;
use AndrewSvirin\Ebics\Services\ZipService;
use Closure;
use DOMDocument;
use DOMElement;

/**
 * Class DataTransferBuilder builder for request container.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class DataTransferBuilder
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
     * @var ZipService
     */
    private $zipService;

    /**
     * @var CryptService
     */
    private $cryptService;

    public function __construct(DOMDocument $dom = null)
    {
        $this->dom = $dom;
        $this->zipService = new ZipService();
        $this->cryptService = new CryptService();
    }

    public function createInstance(): DataTransferBuilder
    {
        $this->instance = $this->dom->createElement('DataTransfer');

        return $this;
    }

    public function addOrderData(OrderDataInterface $orderData): DataTransferBuilder
    {
        $orderDataCompressed = $this->zipService->compress($orderData->getContent());
        $orderDataCompressedEncoded = base64_encode($orderDataCompressed);

        $xmlDataTransfer = $this->dom->createElement('OrderData');
        $xmlDataTransfer->nodeValue = $orderDataCompressedEncoded;
        $this->instance->appendChild($xmlDataTransfer);

        return $this;
    }

    public function addDataEncryptionInfo(Closure $callable = null): DataTransferBuilder
    {
        $orderDetailsBuilder = new DataEncryptionInfoBuilder($this->dom);
        $this->instance->appendChild($orderDetailsBuilder->createInstance()->getInstance());

        call_user_func($callable, $orderDetailsBuilder);

        return $this;
    }

    public function addSignatureData(SignatureDataInterface $userSignature, string $transactionKey): DataTransferBuilder
    {
        $userSignatureCompressed = $this->zipService->compress($userSignature->getContent());
        $userSignatureCompressedEncrypted = $this->cryptService->encryptByKey(
            $transactionKey,
            $userSignatureCompressed
        );
        $userSignatureCompressedEncryptedEncoded = base64_encode($userSignatureCompressedEncrypted);

        $xmlSignatureData = $this->dom->createElement('SignatureData');
        $xmlSignatureData->setAttribute('authenticate', 'true');
        $xmlSignatureData->nodeValue = $userSignatureCompressedEncryptedEncoded;
        $this->instance->appendChild($xmlSignatureData);

        return $this;
    }

    public function getInstance(): DOMElement
    {
        return $this->instance;
    }
}

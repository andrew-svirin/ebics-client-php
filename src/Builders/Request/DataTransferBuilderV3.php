<?php

namespace AndrewSvirin\Ebics\Builders\Request;

/**
 * Ebics 3.0 Class DataTransferBuilder builder for request container.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class DataTransferBuilderV3 extends DataTransferBuilder
{
    public function addDataDigest(string $signatureVersion, string $digest = null): DataTransferBuilder
    {
        $xmlDataDigest = $this->dom->createElement('DataDigest');
        $xmlDataDigest->setAttribute('SignatureVersion', $signatureVersion);
        $this->instance->appendChild($xmlDataDigest);

        if (null !== $digest) {
            $xmlDataDigest->nodeValue = base64_encode($digest);
        }

        return $this;
    }

    public function addAdditionalOrderInfo(): DataTransferBuilder
    {
        $xmlAdditionalOrderInfo = $this->dom->createElement('AdditionalOrderInfo');
        $this->instance->appendChild($xmlAdditionalOrderInfo);

        return $this;
    }
}

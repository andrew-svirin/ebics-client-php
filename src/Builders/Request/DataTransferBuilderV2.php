<?php

namespace AndrewSvirin\Ebics\Builders\Request;

/**
 * Ebics 2.5 Class DataTransferBuilder builder for request container.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class DataTransferBuilderV2 extends DataTransferBuilder
{
    public function addDataDigest(string $signatureVersion, string $digest = null): DataTransferBuilder
    {
        // Skipped.
        return $this;
    }

    public function addAdditionalOrderInfo(): DataTransferBuilder
    {
        // Skipped.
        return $this;
    }
}

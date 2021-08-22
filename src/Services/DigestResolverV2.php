<?php

namespace AndrewSvirin\Ebics\Services;

use AndrewSvirin\Ebics\Contracts\SignatureInterface;

/**
 * Ebics 2.5 DigestResolver.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class DigestResolverV2 extends DigestResolver
{
    public function digest(SignatureInterface $signature, string $algorithm = 'sha256'): string
    {
        return $this->cryptService->calculateDigest($signature, $algorithm, true);
    }
}

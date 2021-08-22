<?php

namespace AndrewSvirin\Ebics\Services;

use AndrewSvirin\Ebics\Contracts\SignatureInterface;

/**
 * Ebics 3.0 DigestResolver.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class DigestResolverV3 extends DigestResolver
{
    public function digest(SignatureInterface $signature, string $algorithm = 'sha256'): string
    {
        return $this->cryptService->calculateCertificateFingerprint(
            $signature->getCertificateContent(),
            $algorithm,
            true
        );
    }
}

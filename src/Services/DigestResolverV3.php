<?php

namespace AndrewSvirin\Ebics\Services;

use AndrewSvirin\Ebics\Contracts\SignatureInterface;

/**
 * Ebics 3.0 DigestResolver.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class DigestResolverV3 extends DigestResolver
{
    public function signDigest(SignatureInterface $signature, string $algorithm = 'sha256'): string
    {
        return $this->cryptService->calculateCertificateFingerprint(
            $signature->getCertificateContent(),
            $algorithm
        );
    }

    public function confirmDigest(SignatureInterface $signature, string $algorithm = 'sha256'): string
    {
        return bin2hex($this->cryptService->calculateCertificateFingerprint(
            $signature->getCertificateContent(),
            $algorithm
        ));
    }
}

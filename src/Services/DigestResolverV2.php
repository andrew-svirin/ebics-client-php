<?php

namespace AndrewSvirin\Ebics\Services;

use AndrewSvirin\Ebics\Contracts\SignatureInterface;

/**
 * Ebics 2.4 & 2.5 DigestResolver.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class DigestResolverV2 extends DigestResolver
{
    public function signDigest(SignatureInterface $signature, string $algorithm = 'sha256'): string
    {
        return $this->cryptService->calculateDigest($signature, $algorithm);
    }

    public function confirmDigest(SignatureInterface $signature, string $algorithm = 'sha256'): string
    {
        if (($certificateContent = $signature->getCertificateContent())) {
            $digest = $this->cryptService->calculateCertificateFingerprint($certificateContent, $algorithm);
        } else {
            $digest = $this->cryptService->calculateDigest($signature, $algorithm);
        }

        return bin2hex($digest);
    }
}

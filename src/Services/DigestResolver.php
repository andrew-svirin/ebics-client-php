<?php

namespace EbicsApi\Ebics\Services;

use EbicsApi\Ebics\Contracts\SignatureInterface;

/**
 * Resolve digest value.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
abstract class DigestResolver
{
    protected CryptService $cryptService;

    public function __construct(CryptService $cryptService)
    {
        $this->cryptService = $cryptService;
    }

    /**
     * Digest for signing orders.
     *
     * @param SignatureInterface $signature
     * @param string $algorithm
     *
     * @return string
     */
    abstract public function signDigest(SignatureInterface $signature, string $algorithm = 'sha256'): string;

    /**
     * Digest for confirmation letter.
     *
     * @param SignatureInterface $signature
     * @param string $algorithm
     *
     * @return string
     */
    abstract public function confirmDigest(SignatureInterface $signature, string $algorithm = 'sha256'): string;
}

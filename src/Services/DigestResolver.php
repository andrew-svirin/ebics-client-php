<?php

namespace AndrewSvirin\Ebics\Services;

use AndrewSvirin\Ebics\Contracts\SignatureInterface;

/**
 * Resolve digest value.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
abstract class DigestResolver
{
    protected CryptService $cryptService;

    public function __construct()
    {
        $this->cryptService = new CryptService();
    }

    abstract public function digest(SignatureInterface $signature, string $algorithm = 'sha256'): string;
}

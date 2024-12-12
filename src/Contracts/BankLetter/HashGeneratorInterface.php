<?php

namespace EbicsApi\Ebics\Contracts\BankLetter;

use EbicsApi\Ebics\Contracts\SignatureInterface;

/**
 * EBICS Generate hash for bank letter.
 * Strategy pattern.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
interface HashGeneratorInterface
{

    /**
     * Generate hash.
     *
     * @param SignatureInterface $signature
     *
     * @return string
     */
    public function generate(SignatureInterface $signature): string;
}

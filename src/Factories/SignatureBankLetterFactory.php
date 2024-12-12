<?php

namespace EbicsApi\Ebics\Factories;

use EbicsApi\Ebics\Models\SignatureBankLetter;

/**
 * Class SignatureBankLetterFactory represents producers for the @see SignatureBankLetter.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class SignatureBankLetterFactory
{
    /**
     * @param string $type
     * @param string $version
     * @param string $exponent
     * @param string $modulus
     * @param string $keyHash
     * @param int $modulusSize
     *
     * @return SignatureBankLetter
     */
    public function create(
        string $type,
        string $version,
        string $exponent,
        string $modulus,
        string $keyHash,
        int $modulusSize
    ): SignatureBankLetter {
        return new SignatureBankLetter(
            $type,
            $version,
            $exponent,
            $modulus,
            $keyHash,
            $modulusSize
        );
    }
}

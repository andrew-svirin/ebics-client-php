<?php

namespace AndrewSvirin\Ebics\Factories;

use AndrewSvirin\Ebics\Models\CertificateBankLetter;

/**
 * Class CertificateBankLetterFactory represents producers for the @see CertificateBankLetter.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class CertificateBankLetterFactory
{

    /**
     * @param string $certificateVersion
     * @param string $exponent
     * @param string $modulus
     * @param string $keyHash
     *
     * @return CertificateBankLetter
     */
    public function create(
        string $certificateVersion,
        string $exponent,
        string $modulus,
        string $keyHash
    ): CertificateBankLetter {
        return new CertificateBankLetter($certificateVersion, $exponent, $modulus, $keyHash);
    }
}

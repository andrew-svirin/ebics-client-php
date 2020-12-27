<?php

namespace AndrewSvirin\Ebics\Factories;

use AndrewSvirin\Ebics\Models\BankLetter;
use AndrewSvirin\Ebics\Models\CertificateBankLetter;

/**
 * Class BankLetterFactory represents producers for the @see BankLetter.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class BankLetterFactory
{

    /**
     * @param CertificateBankLetter $certificateBankLetterA
     * @param CertificateBankLetter $certificateBankLetterE
     * @param CertificateBankLetter $certificateBankLetterX
     *
     * @return BankLetter
     */
    public function create(
        CertificateBankLetter $certificateBankLetterA,
        CertificateBankLetter $certificateBankLetterE,
        CertificateBankLetter $certificateBankLetterX
    ): BankLetter {
        return new BankLetter(
            $certificateBankLetterA,
            $certificateBankLetterE,
            $certificateBankLetterX
        );
    }
}

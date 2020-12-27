<?php

namespace AndrewSvirin\Ebics\Models;

/**
 * EBICS bank letter.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class BankLetter
{
    private $certificateBankLetterA;

    private $certificateBankLetterE;

    private $certificateBankLetterX;

    public function __construct(
        CertificateBankLetter $certificateBankLetterA,
        CertificateBankLetter $certificateBankLetterE,
        CertificateBankLetter $certificateBankLetterX
    ) {
        $this->certificateBankLetterA = $certificateBankLetterA;
        $this->certificateBankLetterE = $certificateBankLetterE;
        $this->certificateBankLetterX = $certificateBankLetterX;
    }

    /**
     * @return CertificateBankLetter
     */
    public function getCertificateBankLetterA(): CertificateBankLetter
    {
        return $this->certificateBankLetterA;
    }

    /**
     * @return CertificateBankLetter
     */
    public function getCertificateBankLetterE(): CertificateBankLetter
    {
        return $this->certificateBankLetterE;
    }

    /**
     * @return CertificateBankLetter
     */
    public function getCertificateBankLetterX(): CertificateBankLetter
    {
        return $this->certificateBankLetterX;
    }
}

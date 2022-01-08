<?php

namespace AndrewSvirin\Ebics\Factories;

use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\BankLetter;
use AndrewSvirin\Ebics\Models\SignatureBankLetter;
use AndrewSvirin\Ebics\Models\User;

/**
 * Class BankLetterFactory represents producers for the @see BankLetter.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class BankLetterFactory
{
    /**
     * @param Bank $bank
     * @param User $user
     * @param SignatureBankLetter $signatureBankLetterA
     * @param SignatureBankLetter $signatureBankLetterE
     * @param SignatureBankLetter $signatureBankLetterX
     *
     * @return BankLetter
     */
    public function create(
        Bank $bank,
        User $user,
        SignatureBankLetter $signatureBankLetterA,
        SignatureBankLetter $signatureBankLetterE,
        SignatureBankLetter $signatureBankLetterX
    ): BankLetter {
        return new BankLetter(
            $bank,
            $user,
            $signatureBankLetterA,
            $signatureBankLetterE,
            $signatureBankLetterX
        );
    }
}

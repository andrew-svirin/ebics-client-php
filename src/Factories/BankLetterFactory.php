<?php

namespace EbicsApi\Ebics\Factories;

use EbicsApi\Ebics\Models\Bank;
use EbicsApi\Ebics\Models\BankLetter;
use EbicsApi\Ebics\Models\SignatureBankLetter;
use EbicsApi\Ebics\Models\User;

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

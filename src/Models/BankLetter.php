<?php

namespace EbicsApi\Ebics\Models;

/**
 * EBICS bank letter.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class BankLetter
{
    private Bank $bank;
    private User $user;
    private SignatureBankLetter $signatureBankLetterA;
    private SignatureBankLetter $signatureBankLetterE;
    private SignatureBankLetter $signatureBankLetterX;

    public function __construct(
        Bank $bank,
        User $user,
        SignatureBankLetter $signatureBankLetterA,
        SignatureBankLetter $signatureBankLetterE,
        SignatureBankLetter $signatureBankLetterX
    ) {
        $this->signatureBankLetterA = $signatureBankLetterA;
        $this->signatureBankLetterE = $signatureBankLetterE;
        $this->signatureBankLetterX = $signatureBankLetterX;
        $this->bank = $bank;
        $this->user = $user;
    }

    /**
     * @return Bank
     */
    public function getBank(): Bank
    {
        return $this->bank;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return SignatureBankLetter
     */
    public function getSignatureBankLetterA(): SignatureBankLetter
    {
        return $this->signatureBankLetterA;
    }

    /**
     * @return SignatureBankLetter
     */
    public function getSignatureBankLetterE(): SignatureBankLetter
    {
        return $this->signatureBankLetterE;
    }

    /**
     * @return SignatureBankLetter
     */
    public function getSignatureBankLetterX(): SignatureBankLetter
    {
        return $this->signatureBankLetterX;
    }
}

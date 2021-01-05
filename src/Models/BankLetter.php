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
    /**
     * @var Bank
     */
    private $bank;

    /**
     * @var User
     */
    private $user;

    /**
     * @var SignatureBankLetter
     */
    private $signatureBankLetterA;

    /**
     * @var SignatureBankLetter
     */
    private $signatureBankLetterE;
    /**
     * @var SignatureBankLetter
     */
    private $signatureBankLetterX;

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

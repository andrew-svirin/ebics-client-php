<?php

namespace AndrewSvirin\Ebics;

use AndrewSvirin\Ebics\Contracts\BankLetterFormatterInterface;
use AndrewSvirin\Ebics\Factories\BankLetterFactory;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\BankLetter;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Models\User;
use AndrewSvirin\Ebics\Services\BankLetterService;

/**
 * EBICS bank letter prepare.
 * Initialization letter details.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class EbicsBankLetter
{

    /**
     * @var BankLetterService
     */
    private $bankLetterService;

    /**
     * @var BankLetterFactory
     */
    private $bankLetterFactory;

    public function __construct()
    {
        $this->bankLetterService = new BankLetterService();
        $this->bankLetterFactory = new BankLetterFactory();
    }

    /**
     * Prepare variables for bank letter.
     * On this moment should be called INI and HEA.
     *
     * @param Bank $bank
     * @param User $user
     * @param KeyRing $keyRing
     *
     * @return BankLetter
     */
    public function prepareBankLetter(Bank $bank, User $user, KeyRing $keyRing): BankLetter
    {
        $bankLetter = $this->bankLetterFactory->create(
            $bank,
            $user,
            $this->bankLetterService->formatSignatureForBankLetter(
                $keyRing->getUserSignatureA(),
                $keyRing->getUserSignatureAVersion()
            ),
            $this->bankLetterService->formatSignatureForBankLetter(
                $keyRing->getUserSignatureE(),
                $keyRing->getUserSignatureEVersion()
            ),
            $this->bankLetterService->formatSignatureForBankLetter(
                $keyRing->getUserSignatureX(),
                $keyRing->getUserSignatureXVersion()
            )
        );

        return $bankLetter;
    }

    /**
     * Format bank letter.
     *
     * @param BankLetter $bankLetter
     * @param BankLetterFormatterInterface $formatter
     *
     * @return mixed
     */
    public function formatBankLetter(BankLetter $bankLetter, BankLetterFormatterInterface $formatter)
    {
        return $formatter->format($bankLetter);
    }
}

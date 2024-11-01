<?php

namespace AndrewSvirin\Ebics;

use AndrewSvirin\Ebics\Contracts\BankLetter\FormatterInterface;
use AndrewSvirin\Ebics\Factories\BankLetterFactory;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\BankLetter;
use AndrewSvirin\Ebics\Models\Keyring;
use AndrewSvirin\Ebics\Models\User;
use AndrewSvirin\Ebics\Services\BankLetter\Formatter\HtmlBankLetterFormatter;
use AndrewSvirin\Ebics\Services\BankLetter\Formatter\PdfBankLetterFormatter;
use AndrewSvirin\Ebics\Services\BankLetter\Formatter\TxtBankLetterFormatter;
use AndrewSvirin\Ebics\Services\BankLetterService;
use AndrewSvirin\Ebics\Services\DigestResolverV2;
use AndrewSvirin\Ebics\Services\DigestResolverV3;
use LogicException;

/**
 * EBICS bank letter prepare.
 * Initialization letter details.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class EbicsBankLetter
{
    private BankLetterService $bankLetterService;
    private BankLetterFactory $bankLetterFactory;

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
     * @param Keyring $keyring
     *
     * @return BankLetter
     */
    public function prepareBankLetter(Bank $bank, User $user, Keyring $keyring): BankLetter
    {
        if (Keyring::VERSION_25 === $keyring->getVersion() || Keyring::VERSION_24 === $keyring->getVersion()) {
            $digestResolver = new DigestResolverV2();
        } elseif (Keyring::VERSION_30 === $keyring->getVersion()) {
            $digestResolver = new DigestResolverV3();
        } else {
            throw new LogicException(sprintf('Version "%s" is not implemented', $keyring->getVersion()));
        }

        return $this->bankLetterFactory->create(
            $bank,
            $user,
            $this->bankLetterService->formatSignatureForBankLetter(
                $keyring->getUserSignatureA(),
                $keyring->getUserSignatureAVersion(),
                $digestResolver
            ),
            $this->bankLetterService->formatSignatureForBankLetter(
                $keyring->getUserSignatureE(),
                $keyring->getUserSignatureEVersion(),
                $digestResolver
            ),
            $this->bankLetterService->formatSignatureForBankLetter(
                $keyring->getUserSignatureX(),
                $keyring->getUserSignatureXVersion(),
                $digestResolver
            )
        );
    }

    /**
     * Format bank letter.
     *
     * @param BankLetter $bankLetter
     * @param FormatterInterface $formatter
     *
     * @return string
     */
    public function formatBankLetter(BankLetter $bankLetter, FormatterInterface $formatter): string
    {
        return $formatter->format($bankLetter);
    }

    public function createTxtBankLetterFormatter(): TxtBankLetterFormatter
    {
        return new TxtBankLetterFormatter();
    }

    public function createHtmlBankLetterFormatter(): HtmlBankLetterFormatter
    {
        return new HtmlBankLetterFormatter();
    }

    public function createPdfBankLetterFormatter(): PdfBankLetterFormatter
    {
        return new PdfBankLetterFormatter();
    }
}

<?php

namespace AndrewSvirin\Ebics\Services\BankLetter\Formatter;

use AndrewSvirin\Ebics\Contracts\BankLetter\FormatterInterface;
use AndrewSvirin\Ebics\Factories\PdfFactory;
use AndrewSvirin\Ebics\Models\BankLetter;

/**
 * Bank letter PDF formatter.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @internal
 */
final class PdfBankLetterFormatter implements FormatterInterface
{
    /**
     * @var HtmlBankLetterFormatter
     */
    private $bankLetterFormatterHtml;

    /**
     * @var PdfFactory
     */
    private $pdfFactory;

    public function __construct(array $translations = [], string $style = '')
    {
        $this->bankLetterFormatterHtml = new HtmlBankLetterFormatter($translations, $style);
        $this->pdfFactory = new PdfFactory();
    }

    /**
     * @inheritDoc
     */
    public function format(BankLetter $bankLetter)
    {
        $html = $this->bankLetterFormatterHtml->format($bankLetter);

        $pdf = $this->pdfFactory->createFromHtml($html);

        return $pdf->outputString();
    }
}

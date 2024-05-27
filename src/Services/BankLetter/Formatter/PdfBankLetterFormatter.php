<?php

namespace AndrewSvirin\Ebics\Services\BankLetter\Formatter;

use AndrewSvirin\Ebics\Contracts\BankLetter\FormatterInterface;
use AndrewSvirin\Ebics\Contracts\PdfFactoryInterface;
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
    private HtmlBankLetterFormatter $bankLetterFormatterHtml;
    private PdfFactoryInterface $pdfFactory;

    public function __construct(PdfFactoryInterface $pdfFactory)
    {
        $this->bankLetterFormatterHtml = new HtmlBankLetterFormatter();
        $this->pdfFactory = $pdfFactory;
    }

    /**
     * Set translations.
     *
     * @param array $translations
     *
     * @return void
     */
    public function setTranslations(array $translations): void
    {
        $this->bankLetterFormatterHtml->setTranslations($translations);
    }

    /**
     * Set additional CSS style.
     *
     * @param string $style
     *
     * @return void
     */
    public function setStyle(string $style): void
    {
        $this->bankLetterFormatterHtml->setStyle($style);
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

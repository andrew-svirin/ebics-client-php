<?php

namespace AndrewSvirin\Ebics\Services\BankLetterFormatter;

use AndrewSvirin\Ebics\Contracts\BankLetterFormatterInterface;
use AndrewSvirin\Ebics\Factories\PdfFactory;
use AndrewSvirin\Ebics\Models\BankLetter;

/**
 * Bank letter PDF formatter.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class BankLetterFormatterPdf implements BankLetterFormatterInterface
{

    /**
     * @var BankLetterFormatterHtml
     */
    private $bankLetterFormatterHtml;

    /**
     * @var PdfFactory
     */
    private $pdfFactory;

    public function __construct()
    {
        $this->bankLetterFormatterHtml = new BankLetterFormatterHtml();
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

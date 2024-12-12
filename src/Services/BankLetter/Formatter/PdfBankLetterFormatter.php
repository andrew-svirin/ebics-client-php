<?php

namespace EbicsApi\Ebics\Services\BankLetter\Formatter;

use EbicsApi\Ebics\Models\BankLetter;
use EbicsApi\Ebics\Models\Pdf;
use EbicsApi\Ebics\Models\SignatureBankLetter;

/**
 * Bank letter PDF formatter.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @internal
 */
final class PdfBankLetterFormatter extends LetterFormatter
{
    /**
     * @inheritDoc
     */
    public function format(BankLetter $bankLetter): string
    {
        $pdf = new Pdf();

        $pdf->setHeader("EBICS {$this->translations['init_letter']}");
        $pdf->totalPages(3);

        $pdf->newPage();

        $pdf->h($this->translations['init_letter'], 16, 255, 0);

        $pdf->c($this->translations['server_name'], 'B', false);
        $pdf->c($this->getServerName($bankLetter), '', true, 50);

        $pdf->c($this->translations['host_id'], 'B', false);
        $pdf->c($bankLetter->getBank()->getHostId(), '', true, 50);

        $pdf->c($this->translations['partner_id'], 'B', false);
        $pdf->c($bankLetter->getUser()->getPartnerId(), '', true, 50);

        $pdf->c($this->translations['user_id'], 'B', false);
        $pdf->c($bankLetter->getUser()->getUserId(), '', true, 50);

        $pdf->h("{$this->translations['init_letter']} INI", 13, 255, 60);
        $this->formatSection($bankLetter->getSignatureBankLetterA(), $pdf);

        $pdf->newPage();

        $pdf->h("{$this->translations['init_letter']} HIA", 13, 255, 60);
        $this->formatSection($bankLetter->getSignatureBankLetterE(), $pdf);
        $pdf->newPage();
        $this->formatSection($bankLetter->getSignatureBankLetterX(), $pdf);

        $pdf->Close();

        return $pdf->outputPDF();
    }

    /**
     * Format signature section.
     *
     * @param SignatureBankLetter $signatureBankLetter
     * @param Pdf $pdf
     *
     * @return void
     */
    private function formatSection(SignatureBankLetter $signatureBankLetter, Pdf $pdf): void
    {
        $pdf->h($this->getSignatureName($signatureBankLetter), 11, 255, 90);
        $pdf->h($this->translations['parameters'], 9, 255, 140);

        $pdf->c($this->translations['version'], 'B', false);
        $pdf->c($signatureBankLetter->getVersion(), '', true, 50);

        $pdf->c($this->translations['date'], 'B', false);
        $pdf->c($this->getCertificateCreatedAt($signatureBankLetter), '', true, 50);

        $this->formatSectionFromCertificate($signatureBankLetter, $pdf);

        $pdf->h("{$this->translations['hash']} (SHA-256)", 9, 255, 140);

        $this->formatBytes($signatureBankLetter->getKeyHash(), $pdf);
    }

    /**
     * Format certificate section.
     *
     * @param SignatureBankLetter $signatureBankLetter
     * @param Pdf $pdf
     *
     * @return void
     */
    private function formatSectionFromCertificate(SignatureBankLetter $signatureBankLetter, Pdf $pdf): void
    {
        if ($signatureBankLetter->isCertified()) {
            $pdf->h($this->translations['certificate'], 9, 255, 140);

            $certificateContent = $this->formatCertificateContent($signatureBankLetter->getCertificateContent());
            foreach (str_split($certificateContent, 64) as $line) {
                $pdf->pre($line);
            }
        } else {
            $pdf->h($this->translations['exponent'], 9, 255, 140);

            $this->formatBytes($signatureBankLetter->getExponent(), $pdf);

            $pdf->h("{$this->translations['modulus']} ({$signatureBankLetter->getModulusSize()} bits)", 9, 255, 140);

            $this->formatBytes($signatureBankLetter->getModulus(), $pdf);
        }
    }

    /**
     * Format bytes to chain of pairs for bank format.
     *
     * @param string $bytes
     * @param Pdf $pdf
     */
    private function formatBytes(string $bytes, Pdf $pdf): void
    {
        $bytes = str_replace(' ', '', $bytes);
        $bytes = strtoupper($bytes);
        $bytes = str_split($bytes, 32);
        $bytes = array_map(
            function ($bytes) {
                return implode(' ', str_split($bytes, 2));
            },
            $bytes
        );

        foreach ($bytes as $line) {
            $pdf->pre($line);
        }
    }
}

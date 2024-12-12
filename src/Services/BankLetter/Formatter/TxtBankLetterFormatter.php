<?php

namespace EbicsApi\Ebics\Services\BankLetter\Formatter;

use EbicsApi\Ebics\Models\BankLetter;
use EbicsApi\Ebics\Models\SignatureBankLetter;
use RuntimeException;

/**
 * Bank letter TXT formatter.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @internal
 */
final class TxtBankLetterFormatter extends LetterFormatter
{
    public function format(BankLetter $bankLetter): string
    {
        $result = $this->formatSection($bankLetter->getSignatureBankLetterA());

        $result .= $this->formatSection($bankLetter->getSignatureBankLetterE());

        $result .= $this->formatSection($bankLetter->getSignatureBankLetterX());

        return $result;
    }

    /**
     * Format section for one certificate.
     *
     * @param SignatureBankLetter $signatureBankLetter
     *
     * @return string
     */
    private function formatSection(SignatureBankLetter $signatureBankLetter): string
    {
        if ($signatureBankLetter->isCertified()) {
            $signatureSection = $this->formatSectionFromCertificate($signatureBankLetter);
        } else {
            $signatureSection = $this->formatSectionFromModulusExponent($signatureBankLetter);
        }

        return sprintf(
            "{$this->translations['version']}:\n%s\n%s\n{$this->translations['hash']}:\n%s\n\n",
            $signatureBankLetter->getVersion(),
            $signatureSection,
            $this->formatBytes($signatureBankLetter->getKeyHash())
        );
    }

    private function formatSectionFromCertificate(SignatureBankLetter $certificateBankLetter): string
    {
        return sprintf(
            "{$this->translations['certificate']}:\n%s",
            $this->formatCertificateContent($certificateBankLetter->getCertificateContent())
        );
    }

    private function formatSectionFromModulusExponent(SignatureBankLetter $certificateBankLetter): string
    {
        return sprintf(
            "{$this->translations['exponent']}:\n%s\n{$this->translations['modulus']}:\n%s",
            $this->formatBytes($certificateBankLetter->getExponent()),
            $this->formatBytes($certificateBankLetter->getModulus())
        );
    }

    /**
     * Format bytes to chain of pairs for bank format.
     *
     * @param string $bytes
     *
     * @return string In upper case.
     */
    private function formatBytes(string $bytes): string
    {
        $result = '';
        $newLineNum = 48;
        $newLineChar = "\n";

        // Add fictive space.
        $bytes = ' ' . $bytes;
        $length = strlen($bytes);

        // Prepare result from bytes. Replace every n-character by new line.
        for ($i = 0; $i < $length; $i++) {
            $isNewLine = (0 === $i % $newLineNum);
            if ($isNewLine) {
                if (' ' !== $bytes[$i]) {
                    throw new RuntimeException('Character must be a space.');
                }
                $result[$i] = $newLineChar;
            } else {
                $result[$i] = $bytes[$i];
            }
        }

        // Convert to upper case and trim leading fictive space.
        return strtoupper(trim($result));
    }
}

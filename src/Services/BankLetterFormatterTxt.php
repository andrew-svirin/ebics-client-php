<?php

namespace AndrewSvirin\Ebics\Services;

use AndrewSvirin\Ebics\Contracts\BankLetterFormatterInterface;
use AndrewSvirin\Ebics\Models\BankLetter;
use RuntimeException;

/**
 * Bank letter TXT formatter.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class BankLetterFormatterTxt implements BankLetterFormatterInterface
{

    public function format(BankLetter $bankLetter)
    {
        $result = '';

        $result .= sprintf(
            "Version:\n%s\nExponent:\n%s\nModulus:\n%s\nHash:\n%s\n\n",
            $bankLetter->getCertificateBankLetterA()->getCertificateVersion(),
            $this->formatBytes($bankLetter->getCertificateBankLetterA()->getExponent()),
            $this->formatBytes($bankLetter->getCertificateBankLetterA()->getModulus()),
            $this->formatBytes($bankLetter->getCertificateBankLetterA()->getKeyHash())
        );

        $result .= sprintf(
            "Version:\n%s\nExponent:\n%s\nModulus:\n%s\nHash:\n%s\n\n",
            $bankLetter->getCertificateBankLetterE()->getCertificateVersion(),
            $this->formatBytes($bankLetter->getCertificateBankLetterE()->getExponent()),
            $this->formatBytes($bankLetter->getCertificateBankLetterE()->getModulus()),
            $this->formatBytes($bankLetter->getCertificateBankLetterE()->getKeyHash())
        );

        $result .= sprintf(
            "Version:\n%s\nExponent:\n%s\nModulus:\n%s\nHash:\n%s\n\n",
            $bankLetter->getCertificateBankLetterX()->getCertificateVersion(),
            $this->formatBytes($bankLetter->getCertificateBankLetterX()->getExponent()),
            $this->formatBytes($bankLetter->getCertificateBankLetterX()->getModulus()),
            $this->formatBytes($bankLetter->getCertificateBankLetterX()->getKeyHash())
        );

        return $result;
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

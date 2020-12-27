<?php

namespace AndrewSvirin\Ebics\Services;

use AndrewSvirin\Ebics\Factories\CertificateBankLetterFactory;
use AndrewSvirin\Ebics\Models\Certificate;
use AndrewSvirin\Ebics\Models\CertificateBankLetter;

/**
 * Bank letter helper functions.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class BankLetterService
{

    /**
     * @var CryptService
     */
    private $cryptService;

    /**
     * @var CertificateBankLetterFactory
     */
    private $certificateBankLetterFactory;

    public function __construct()
    {
        $this->cryptService = new CryptService();
        $this->certificateBankLetterFactory = new CertificateBankLetterFactory();
    }

    /**
     * @param Certificate $certificate
     * @param string $certificateVersion
     *
     * @return CertificateBankLetter
     */
    public function formatCertificateForBankLetter(
        Certificate $certificate,
        string $certificateVersion
    ): CertificateBankLetter {
        $publicKeyDetails = $this->cryptService->getPublicKeyDetails($certificate->getPublicKey());

        $exponentFormatted = $this->formatBytesForBank($publicKeyDetails['e']);
        $modulusFormatted = $this->formatBytesForBank($publicKeyDetails ['m']);

        $key = $this->cryptService->calculateKey($exponentFormatted, $modulusFormatted);
        $keyHash = $this->cryptService->calculateKeyHash($key);
        $keyHashFormatted = $this->formatKeyHashForBankLetter($keyHash);

        return $this->certificateBankLetterFactory->create(
            $certificateVersion,
            $exponentFormatted,
            $modulusFormatted,
            $keyHashFormatted
        );
    }

    /**
     * @param string $hash
     *
     * @return string
     */
    private function formatKeyHashForBankLetter(string $hash): string
    {
        // Split hash by 2 bytes in array and join by space character.
        $hash = join(' ', str_split($hash, 2));

        return $hash;
    }

    /**
     * Format bytes to chain of pairs for bank format.
     *
     * @param string $bytes
     *
     * @return string In upper case.
     */
    private function formatBytesForBank(string $bytes): string
    {
        $result = '';

        // Go over pairs of bytes.
        foreach ($this->cryptService->binToArray($bytes) as $byte) {
            // Convert to lover case hexadecimal number.
            $result .= sprintf('%02x ', $byte);
        }

        return trim($result);
    }
}

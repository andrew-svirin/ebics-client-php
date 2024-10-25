<?php

namespace AndrewSvirin\Ebics\Services;

use AndrewSvirin\Ebics\Contracts\BankLetter\HashGeneratorInterface;
use AndrewSvirin\Ebics\Contracts\SignatureInterface;
use AndrewSvirin\Ebics\Factories\CertificateX509Factory;
use AndrewSvirin\Ebics\Factories\SignatureBankLetterFactory;
use AndrewSvirin\Ebics\Models\SignatureBankLetter;

/**
 * Bank letter helper functions.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @internal
 */
final class BankLetterService
{
    private CryptService $cryptService;
    private SignatureBankLetterFactory $signatureBankLetterFactory;
    private CertificateX509Factory $certificateX509Factory;

    public function __construct()
    {
        $this->cryptService = new CryptService();
        $this->signatureBankLetterFactory = new SignatureBankLetterFactory();
        $this->certificateX509Factory = new CertificateX509Factory();
    }

    /**
     * @param SignatureInterface $signature
     * @param string $version
     *
     * @param HashGeneratorInterface $hashGenerator
     *
     * @return SignatureBankLetter
     */
    public function formatSignatureForBankLetter(
        SignatureInterface $signature,
        string $version,
        HashGeneratorInterface $hashGenerator
    ): SignatureBankLetter {
        $publicKeyDetails = $this->cryptService->getPublicKeyDetails($signature->getPublicKey());

        $exponentFormatted = $this->formatBytesForBank($publicKeyDetails['e']);
        $modulusFormatted = $this->formatBytesForBank($publicKeyDetails['m']);

        $keyHash = $hashGenerator->generate($signature);
        $keyHashFormatted = $this->formatKeyHashForBankLetter($keyHash);

        $modulusSize = strlen($publicKeyDetails['m']) * 8; // 8 bits in byte.
        $signatureBankLetter = $this->signatureBankLetterFactory->create(
            $signature->getType(),
            $version,
            $exponentFormatted,
            $modulusFormatted,
            $keyHashFormatted,
            $modulusSize
        );

        if (($content = $signature->getCertificateContent())) {
            $certificateX509 = $this->certificateX509Factory->createFromContent($content);
            $startDate = $certificateX509->getValidityStartDate();

            $signatureBankLetter->setCertificateContent($content);
            $signatureBankLetter->setCertificateCreatedAt($startDate);
        }

        return $signatureBankLetter;
    }

    /**
     * @param string $hash
     *
     * @return string
     */
    private function formatKeyHashForBankLetter(string $hash): string
    {
        // Split hash by 2 bytes in array and join by space character.
        $hash = implode(' ', str_split($hash, 2));

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
            // Convert to lover case hexadecimal number and add a space.
            $result .= sprintf('%02x ', $byte);
        }

        return trim($result);
    }
}

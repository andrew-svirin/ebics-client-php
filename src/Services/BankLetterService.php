<?php

namespace EbicsApi\Ebics\Services;

use EbicsApi\Ebics\Contracts\SignatureInterface;
use EbicsApi\Ebics\Factories\CertificateX509Factory;
use EbicsApi\Ebics\Factories\SignatureBankLetterFactory;
use EbicsApi\Ebics\Models\SignatureBankLetter;

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

    public function __construct(
        CryptService $cryptService,
        SignatureBankLetterFactory $signatureBankLetterFactory,
        CertificateX509Factory $certificateX509Factory
    ) {
        $this->cryptService = $cryptService;
        $this->signatureBankLetterFactory = $signatureBankLetterFactory;
        $this->certificateX509Factory = $certificateX509Factory;
    }

    /**
     * @param SignatureInterface $signature
     * @param string $version
     * @param DigestResolver $digestResolver
     *
     * @return SignatureBankLetter
     */
    public function formatSignatureForBankLetter(
        SignatureInterface $signature,
        string $version,
        DigestResolver $digestResolver
    ): SignatureBankLetter {
        $publicKeyDetails = $this->cryptService->getPublicKeyDetails($signature->getPublicKey());

        $exponentFormatted = $this->formatBytesForBank($publicKeyDetails['e']);
        $modulusFormatted = $this->formatBytesForBank($publicKeyDetails['m']);

        $keyHash = $digestResolver->confirmDigest($signature);
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

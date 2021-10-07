<?php

namespace AndrewSvirin\Ebics\Services\BankLetter\HashGenerator;

use AndrewSvirin\Ebics\Contracts\BankLetter\HashGeneratorInterface;
use AndrewSvirin\Ebics\Contracts\SignatureInterface;
use AndrewSvirin\Ebics\Services\CryptService;

/**
 * Generate hash for public key.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @internal
 */
class PublicKeyHashGenerator implements HashGeneratorInterface
{

    /**
     * @var CryptService
     */
    private $cryptService;

    public function __construct()
    {
        $this->cryptService = new CryptService();
    }

    /**
     * @inheritDoc
     */
    public function generate(SignatureInterface $signature): string
    {
        $publicKeyDetails = $this->cryptService->getPublicKeyDetails($signature->getPublicKey());

        $e = $this->formatBytesToHex($publicKeyDetails['e']);
        $m = $this->formatBytesToHex($publicKeyDetails['m']);

        $key = $this->cryptService->calculateKey($e, $m);

        $hash = $this->cryptService->calculateCertificateFingerprint($key);

        return $hash;
    }

    /**
     * Convert bytes to hex.
     *
     * @param string $bytes Bytes
     *
     * @return string
     */
    private function formatBytesToHex(string $bytes): string
    {
        $out = '';

        // Go over pairs of bytes.
        foreach ($this->cryptService->binToArray($bytes) as $byte) {
            // Convert to lover case hexadecimal number.
            $out .= sprintf("%02x", $byte);
        }

        return trim($out);
    }
}

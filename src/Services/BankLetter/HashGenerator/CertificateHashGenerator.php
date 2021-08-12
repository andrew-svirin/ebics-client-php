<?php

namespace AndrewSvirin\Ebics\Services\BankLetter\HashGenerator;

use AndrewSvirin\Ebics\Contracts\BankLetter\HashGeneratorInterface;
use AndrewSvirin\Ebics\Contracts\SignatureInterface;
use AndrewSvirin\Ebics\Services\CryptService;

/**
 * Generate hash for certificate.
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @internal
 */
class CertificateHashGenerator implements HashGeneratorInterface
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
        $key = $signature->getCertificateContent();

        return $this->cryptService->calculateCertificateFingerprint($key);
    }
}

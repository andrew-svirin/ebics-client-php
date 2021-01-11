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

        $key = $this->cryptService->calculateKey(
            $publicKeyDetails['e'],
            $publicKeyDetails['m']
        );

        return $this->cryptService->calculateKeyHash($key);
    }
}

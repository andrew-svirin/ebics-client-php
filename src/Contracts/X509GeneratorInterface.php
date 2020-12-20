<?php

namespace AndrewSvirin\Ebics\Contracts;

use AndrewSvirin\Ebics\Contracts\Crypt\RSAInterface;

/**
 * X509 Factory Interface representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
interface X509GeneratorInterface
{
    /**
     * Generate a X509 certificate and returns its content
     *
     * @param RSAInterface $privateKey the private key
     * @param RSAInterface $publicKey the public key
     * @param array $options optional generation options (may be empty)
     *
     * @return string the X509 content
     */
    public function generateX509(RSAInterface $privateKey, RSAInterface $publicKey, array $options = []): string;
}

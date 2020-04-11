<?php

namespace AndrewSvirin\Ebics\Contracts;

use phpseclib\Crypt\RSA;

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
     * @param RSA   $privateKey the private key
     * @param RSA   $publicKey  the public key
     * @param array $options    optional generation options (may be empty)
     *
     * @return string the X509 content
     */
    public function generateX509(RSA $privateKey, RSA $publicKey, array $options = []): string;
}

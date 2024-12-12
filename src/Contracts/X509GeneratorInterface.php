<?php

namespace EbicsApi\Ebics\Contracts;

use EbicsApi\Ebics\Contracts\Crypt\RSAInterface;
use EbicsApi\Ebics\Contracts\Crypt\X509Interface;

/**
 * X509 Factory Interface representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
interface X509GeneratorInterface
{

    /**
     * Generate a X509 (Authorization) and returns its content.
     *
     * @param RSAInterface $privateKey
     * @param RSAInterface $publicKey
     *
     * @return X509Interface
     */
    public function generateAX509(RSAInterface $privateKey, RSAInterface $publicKey): X509Interface;

    /**
     * Generate a X509 (Authorization) and returns its content.
     *
     * @param RSAInterface $privateKey
     * @param RSAInterface $publicKey
     *
     * @return X509Interface
     */
    public function generateEX509(RSAInterface $privateKey, RSAInterface $publicKey): X509Interface;

    /**
     * Generate a X509 (Authorization) and returns its content.
     *
     * @param RSAInterface $privateKey
     * @param RSAInterface $publicKey
     *
     * @return X509Interface
     */
    public function generateXX509(RSAInterface $privateKey, RSAInterface $publicKey): X509Interface;
}

<?php

namespace AndrewSvirin\Ebics\Models\Crypt;

use AndrewSvirin\Ebics\Contracts\Crypt\BigIntegerInterface;
use AndrewSvirin\Ebics\Contracts\Crypt\RSAInterface;
use AndrewSvirin\Ebics\Factories\Crypt\BigIntegerFactory;

/**
 * Crypt RSA model.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @property \phpseclib\Math\BigInteger $exponent
 * @property \phpseclib\Math\BigInteger $modulus
 */
class RSA extends \phpseclib\Crypt\RSA implements RSAInterface
{
    /**
     * @see \phpseclib\Crypt\RSA::ENCRYPTION_PKCS1
     */
    const ENCRYPTION_PKCS1 = 2;

    /**
     * @see \phpseclib\Crypt\RSA::PRIVATE_FORMAT_PKCS1
     */
    const PRIVATE_FORMAT_PKCS1 = 0;

    /**
     * @see \phpseclib\Crypt\RSA::PUBLIC_FORMAT_PKCS1
     */
    const PUBLIC_FORMAT_PKCS1 = 4;

    public function getExponent(): BigIntegerInterface
    {
        return BigIntegerFactory::createFromPhpSecLib($this->exponent);
    }

    public function getModulus(): BigIntegerInterface
    {
        return BigIntegerFactory::createFromPhpSecLib($this->modulus);
    }
}

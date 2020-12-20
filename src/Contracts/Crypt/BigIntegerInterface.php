<?php

namespace AndrewSvirin\Ebics\Contracts\Crypt;

/**
 * Crypt BigInteger representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
interface BigIntegerInterface
{

    /**
     * @param bool $twos_compliment
     *
     * @return string
     * @see \phpseclib\Math\BigInteger::toBytes()
     */
    public function toBytes($twos_compliment = false);

    /**
     * @param bool $twos_compliment
     *
     * @return string
     * @see \phpseclib\Math\BigInteger::toHex()
     */
    public function toHex($twos_compliment = false);

    /**
     * @return string
     * @see \phpseclib\Math\BigInteger::toString()
     */
    public function toString();
}

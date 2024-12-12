<?php

namespace EbicsApi\Ebics\Contracts\Crypt;

/**
 * Crypt RSA representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
interface HashInterface
{

    /**
     * Compute the HMAC.
     *
     * @param string $text
     *
     * @return string
     */
    public function hash($text);

    /**
     * Returns the hash length (in bytes)
     *
     * @return int
     */
    public function getLength();
}

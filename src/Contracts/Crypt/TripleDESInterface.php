<?php

namespace EbicsApi\Ebics\Contracts\Crypt;

/**
 * Crypt Triple DES representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
interface TripleDESInterface
{

    /**
     * Sets the key.
     *
     * Keys can be of any length.  Triple DES, itself, can use 128-bit (eg. strlen($key) == 16) or
     * 192-bit (eg. strlen($key) == 24) keys.  This function pads and truncates $key as appropriate.
     *
     * DES also requires that every eighth bit be a parity bit, however, we'll ignore that.
     *
     * If the key is not explicitly set, it'll be assumed to be all null bytes.
     *
     * @param string $key
     *
     * @return void
     */
    public function setKey(string $key);

    /**
     * Sets the initialization vector. (optional)
     *
     * SetIV is not required when self::MODE_ECB is being used.  If not explicitly set, it'll be assumed
     * to be all zero's.
     *
     * @param string $iv
     *
     * @return void
     */
    public function setIV(string $iv);

    /**
     * Decrypts a message.
     *
     * @param string $ciphertext
     *
     * @return string $plaintext
     */
    public function decrypt(string $ciphertext);

    /**
     * Encrypts a message.
     *
     * @param string $plaintext
     *
     * @return string $cipertext
     */
    public function encrypt(string $plaintext);
}

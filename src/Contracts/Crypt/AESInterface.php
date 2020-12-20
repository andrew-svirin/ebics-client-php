<?php

namespace AndrewSvirin\Ebics\Contracts\Crypt;

/**
 * Crypt AES representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
interface AESInterface
{

    /**
     * @param int $length
     *
     * @return void
     * @see \phpseclib\Crypt\AES::setKeyLength()
     */
    public function setKeyLength($length);

    /**
     * @param string $key
     *
     * @return void
     * @see \phpseclib\Crypt\AES::setKey()
     */
    public function setKey($key);

    /**
     * @param string $ciphertext
     *
     * @return string $plaintext
     * @see \phpseclib\Crypt\AES::decrypt()
     */
    public function decrypt($ciphertext);

    /**
     * @param mixed $options
     *
     * @return void
     */
    public function setOpenSSLOptions($options);
}

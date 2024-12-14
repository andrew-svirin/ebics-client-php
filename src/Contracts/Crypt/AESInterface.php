<?php

namespace EbicsApi\Ebics\Contracts\Crypt;

use EbicsApi\Ebics\Contracts\BufferInterface;

/**
 * Crypt AES representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
interface AESInterface
{

    /**
     * Sets the key length
     *
     * Valid key lengths are 128, 192, and 256.  If the length is less than 128, it will be rounded up to
     * 128.  If the length is greater than 128 and invalid, it will be rounded down to the closest valid amount.
     *
     * @param int $length
     *
     * @return void
     */
    public function setKeyLength(int $length);

    /**
     * Sets the key.
     *
     * Rijndael supports five different key lengths, AES only supports three.
     *
     * @param string $key
     *
     * @return void
     */
    public function setKey(string $key);

    /**
     * Sets the initialization vector. (optional)
     *
     * SetIV is not required when self::MODE_ECB (or ie for AES: AES::MODE_ECB) is being used.
     * If not explicitly set, it'll be assumed to be all zero's.
     *
     * @param string $iv
     *
     * @return void
     */
    public function setIV(string $iv);

    /**
     * Encrypts a message.
     *
     * $plaintext will be padded with additional bytes such that it's length is a multiple of
     * the block size. Other cipher implementations may or may not pad in the same manner.
     * Other common approaches to padding and the reasons why it's necessary are discussed in
     * the following URL:
     *
     * {@link http://www.di-mgt.com.au/cryptopad.html http://www.di-mgt.com.au/cryptopad.html}
     *
     * An alternative to padding is to, separately, send the length of the file.  This is what
     * SSH, in fact, does. strlen($plaintext) will still need to be a multiple of the block size,
     * however, arbitrary values can be added to make it that length.
     *
     * @param string $plaintext
     *
     * @return string $ciphertext
     * @internal Could, but not must, extend by the child Crypt_* class
     */
    public function encrypt(string $plaintext);

    /**
     * Decrypts a message.
     *
     * If strlen($ciphertext) is not a multiple of the block size, null bytes will be added
     * to the end of the string until it is.
     *
     * @param BufferInterface $ciphertext
     * @param BufferInterface $plaintext
     *
     * @return void
     */
    public function decryptBuffer(BufferInterface $ciphertext, BufferInterface $plaintext);

    /**
     * Decrypts a message.
     *
     * If strlen($ciphertext) is not a multiple of the block size, null bytes will be added
     * to the end of the string until it is.
     *
     * @param string $ciphertext
     *
     * @return string $plaintext
     */
    public function decrypt(string $ciphertext);

    /**
     * Set options.
     *
     * @param mixed $options
     *
     * @return void
     */
    public function setOpenSSLOptions($options);
}

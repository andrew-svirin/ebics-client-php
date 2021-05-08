<?php

namespace AndrewSvirin\Ebics\Models\Crypt;

use AndrewSvirin\Ebics\Contracts\Crypt\TripleDESInterface;
use LogicException;

/**
 * Pure-PHP implementation of Triple DES.
 *
 * Uses openssl.  Operates in the EDE3 mode (encrypt-decrypt-encrypt).
 */
class TripleDES implements TripleDESInterface
{

    /**
     * @var string
     */
    private $method = 'DES-EDE3-CBC';

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $iv;

    public function setKey($key)
    {
        $this->key = $key;
    }

    public function setIV($iv)
    {
        $this->iv = $iv;
    }

    public function decrypt($ciphertext)
    {
        if (!($decrypted = openssl_decrypt(
            $ciphertext,
            $this->method,
            $this->key,
            OPENSSL_RAW_DATA,
            $this->iv
        ))) {
            throw new LogicException('Decryption failed.');
        }
        return $decrypted;
    }

    public function encrypt($plaintext)
    {
        if (!($encrypted = openssl_encrypt(
            $plaintext,
            $this->method,
            $this->key,
            OPENSSL_RAW_DATA,
            $this->iv
        ))) {
            throw new LogicException('Encryption failed.');
        }
        return $encrypted;
    }
}

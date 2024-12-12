<?php

namespace EbicsApi\Ebics\Models\Crypt;

use EbicsApi\Ebics\Contracts\Crypt\TripleDESInterface;
use LogicException;

/**
 * Pure-PHP implementation of Triple DES.
 *
 * Uses openssl.  Operates in the EDE3 mode (encrypt-decrypt-encrypt).
 */
final class TripleDES implements TripleDESInterface
{
    private string $method = 'DES-EDE3-CBC';
    private string $key;
    private string $iv;

    public function setKey($key)
    {
        $this->key = $key;
    }

    public function setIV($iv)
    {
        $this->iv = $iv;
    }

    public function decrypt($ciphertext): string
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

    public function encrypt($plaintext): string
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

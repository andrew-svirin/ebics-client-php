<?php

namespace EbicsApi\Ebics\Models\Crypt;

use EbicsApi\Ebics\Contracts\Crypt\HashInterface;
use LogicException;

/**
 * Pure-PHP implementations of keyed-hash message authentication codes (HMACs) and various cryptographic hashing
 * functions.
 *
 * Uses hash() or mhash() if available and an internal implementation, otherwise.  Currently supports the following:
 * sha1, sha256
 */
final class Hash implements HashInterface
{
    /**
     * Byte-length of compression blocks / key (Internal HMAC)
     */
    protected int $b;

    /**
     * Byte-length of hash output (Internal HMAC)
     */
    protected int $l;

    /**
     * Hash Algorithm
     */
    protected string $hash;

    /**
     * Key
     */
    protected ?string $key = null;

    /**
     * Computed Key
     */
    protected ?string $computedKey = null;

    /**
     * Default Constructor.
     *
     * @param string $hash
     */
    public function __construct(string $hash = 'sha1')
    {
        $this->setHash($hash);
    }

    public function hash($text): string
    {
        if (!empty($this->key) || is_string($this->key)) {
            $output = hash_hmac($this->hash, $text, $this->computedKey, true);
        } else {
            $output = hash($this->hash, $text, true);
        }

        if (!($hash = substr($output, 0, $this->l))) {
            throw new LogicException('Hash can not be empty.');
        }

        return $hash;
    }

    /**
     * Sets the hash function.
     *
     * @param string $hash
     *
     * @return void
     */
    private function setHash(string $hash)
    {
        switch ($hash) {
            case 'sha1':
                $this->l = 20;
                break;
            case 'sha256':
                $this->l = 32;
                break;
            default:
                throw new LogicException('Hash is not supported');
        }

        switch ($hash) {
            case 'sha1':
            case 'sha256':
                $this->b = 64;
                break;
            default:
                throw new LogicException('Hash is not supported');
        }

        switch ($hash) {
            case 'sha256':
                $this->hash = $hash;
                return;
            case 'sha1':
            default:
                $this->hash = 'sha1';
        }
        $this->computeKey();
    }

    /**
     * Pre-compute the key used by the HMAC
     *
     * Quoting http://tools.ietf.org/html/rfc2104#section-2, "Applications that use keys longer than B bytes
     * will first hash the key using H and then use the resultant L byte string as the actual key to HMAC."
     *
     * As documented in https://www.reddit.com/r/PHP/comments/9nct2l/symfonypolyfill_hash_pbkdf2_correct_fix_for/
     * when doing an HMAC multiple times it's faster to compute the hash once instead of computing it during
     * every call
     *
     * @return void
     */
    private function computeKey()
    {
        if ($this->key === null) {
            $this->computedKey = null;
            return;
        }

        if (strlen($this->key) <= $this->b) {
            $this->computedKey = $this->key;
            return;
        }

        $this->computedKey = hash($this->hash, $this->key, true);
    }

    public function getLength(): int
    {
        return $this->l;
    }
}

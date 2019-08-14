<?php

namespace AndrewSvirin\Ebics;

use Exception;

/**
 * EBICS key ring representation.
 *
 * An EbicsKeyRing instance can hold sets of private user keys and/or public
 * bank keys. Private user keys are always stored AES encrypted by the
 * specified passphrase (derivated by PBKDF2). For each key file on disk or
 * same key dictionary a singleton instance is created.
 */
class EbicsKeyRing
{

    /**
     * The path to a key file.
     * @var string 
     */
    private $_keys;

    /**
     * The passphrase by which all private keys are encrypted/decrypted.
     * @var string 
     */
    private $_passphrase;

    /**
     * Extracted from file keys.
     * @var array 
     */
    private $_extractedKeys = [];

    /**
     * Constructor.
     * @param string $keys
     * @param string $passphrase
     */
    public function __construct($keys, $passphrase)
    {
        $this->_keys = $keys;
        $this->_passphrase = $passphrase;
        $this->_extractKeys();
    }

    /**
     * Extract keys.
     */
    private function _extractKeys()
    {
        $keysRawData = file_get_contents($this->_keys);
        if (!$keysRawData) {
            throw new Exception('Incorrect file path for keys.');
        }
        $keysData = json_decode($keysRawData, true);
        if (!$keysData) {
            throw new Exception('Can\'t decode key data.');
        }
        $this->_extractedKeys = $keysData;
    }

    /**
     * Password phrase.
     * @return string
     */
    public function getPassphrase()
    {
        return $this->_passphrase;
    }

    /**
     * Getter for {keys}.
     * @return array
     */
    public function getKeys()
    {
        return $this->_extractedKeys;
    }

    /**
     * Calculate Public Digest
     *
     * Concat the exponent and modulus (hex representation) with a single whitespace
     * remove leading zeros from both
     * calculate digest (SHA256)
     * encode as Base64
     * 
     * @param integer $exponent
     * @param integer $modulus
     * @return string
     */
    public static function calculatePublicDigest($exponent, $modulus)
    {
        $e = ltrim((string) $exponent, '0');
        $m = ltrim((string) $modulus, '0');
        $concat = $e . ' ' . $m;
        $sha256 = hash('sha256', $concat, TRUE);
        $b64en = base64_encode($sha256);
        return $b64en;
    }

    /**
     * Format key.
     *
     * @param string $key
     * @param string $type ('PUBLIC'|'PRIVATE')
     * @param string $iv (for 'PRIVATE')
     *
     * @return string
     */
    public static function formatKey($key, $type, $iv = NULL)
    {
        switch ($type) {
            case 'PUBLIC':
                $prefix = "-----BEGIN PUBLIC KEY-----\n";
                $suffix = "-----END PUBLIC KEY-----";
                break;
            case 'PRIVATE':
                $prefix = "-----BEGIN PRIVATE KEY-----\nProc-Type: 4,ENCRYPTED\nDEK-Info: DES-EDE3-CBC,{$iv}\n\n";
                $suffix = "-----END PRIVATE KEY-----";
                break;
        }
        $formattedKey = $prefix . chunk_split($key, 64, "\n") . $suffix;

        return $formattedKey;
    }

}

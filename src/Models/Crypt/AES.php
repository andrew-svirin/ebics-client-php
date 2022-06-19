<?php

namespace AndrewSvirin\Ebics\Models\Crypt;

use AndrewSvirin\Ebics\Contracts\Crypt\AESInterface;
use LogicException;

/**
 * Pure-PHP implementation of AES.
 * Able only CBC mode.
 */
final class AES implements AESInterface
{
    /**
     * Base value for the mcrypt implementation $engine switch
     */
    const ENGINE_OPENSSL = 3;

    /**
     * The Key Length (in bytes)
     *
     * @var int
     */
    protected $key_length = 16;

    /**
     * Padding status
     *
     * @var bool
     */
    protected $padding = true;

    /**
     * Is the mode one that is paddable?
     *
     * @var bool
     */
    protected $paddable = false;

    /**
     * Has the key length explicitly been set or should it be derived from the key, itself?
     *
     * @var bool
     */
    protected $explicit_key_length = false;

    /**
     * The Block Length of the block cipher
     *
     * @var int
     */
    protected $block_size = 16;

    /**
     * Holds which crypt engine internaly should be use,
     * which will be determined automatically on __construct()
     *
     * Currently available $engines are:
     * - self::ENGINE_OPENSSL  (very fast, php-extension: openssl, extension_loaded('openssl') required)
     *
     * @var int|null
     */
    protected $engine;

    /**
     * Does internal cipher state need to be (re)initialized?
     *
     * @var bool
     */
    protected $changed = true;

    /**
     * The Key
     *
     * @var string
     */
    protected $key = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";

    /**
     * The Initialization Vector
     *
     * @var string
     */
    protected $iv;

    /**
     * A "sliding" Initialization Vector
     *
     * @var string
     */
    protected $encryptIV;

    /**
     * A "sliding" Initialization Vector
     *
     * @var string
     */
    protected $decryptIV;

    /**
     * The openssl specific name of the cipher in ECB mode
     *
     * If OpenSSL does not support the mode we're trying to use (CTR)
     * it can still be emulated with ECB mode.
     *
     * @link http://www.php.net/openssl-get-cipher-methods
     * @var string
     */
    protected $cipherNameOpensslEcb;

    /**
     * The openssl specific name of the cipher
     *
     * Only used if $engine == self::ENGINE_OPENSSL
     *
     * @link http://www.php.net/openssl-get-cipher-methods
     * @var string
     */
    protected $cipherNameOpenssl;

    /**
     * Determines what options are passed to openssl_encrypt/decrypt
     *
     * @var mixed
     */
    protected $opensslOptions;

    /**
     * Default Constructor.
     *
     * Determines whether or not the mcrypt extension should be used.
     */
    public function __construct()
    {
        // $mode dependent settings
        $this->paddable = true;

        $this->setEngine();
    }

    public function setKeyLength($length)
    {
        switch (true) {
            case $length === 128:
                $this->key_length = 16;
                break;
            case $length === 224:
            case $length === 256:
                $this->key_length = 32;
                break;
            default:
                throw new LogicException('Unhandled length.');
        }

        $this->explicit_key_length = true;
        $this->changed = true;
        $this->setEngine();
    }

    public function setKey($key)
    {
        if (!$this->explicit_key_length) {
            $this->setKeyLength(strlen($key) << 3);
            $this->explicit_key_length = false;
        }

        $this->key = $key;
        $this->changed = true;
        $this->setEngine();

        if (!$this->explicit_key_length) {
            $length = strlen($key);
            switch (true) {
                case $length <= 16:
                    $this->key_length = 16;
                    break;
                case $length <= 24:
                    $this->key_length = 24;
                    break;
                default:
                    $this->key_length = 32;
            }
            $this->setEngine();
        }
    }

    public function setIV($iv)
    {
        $this->iv = $iv;
        $this->changed = true;
    }

    public function encrypt($plaintext)
    {
        if ($this->paddable) {
            $plaintext = $this->pad($plaintext);
        }

        if ($this->changed) {
            $this->clearBuffers();
            $this->changed = false;
        }

        if (!($result = openssl_encrypt(
            $plaintext,
            $this->cipherNameOpenssl,
            $this->key,
            $this->opensslOptions,
            $this->encryptIV
        ))) {
            throw new LogicException('Encryption failed.');
        }
        if (!defined('OPENSSL_RAW_DATA')) {
            $result = substr($result, 0, -$this->block_size);
        }

        return $result;
    }

    /**
     * Pads a string
     *
     * Pads a string using the RSA PKCS padding standards so that its length is a multiple of the blocksize.
     * $this->block_size - (strlen($text) % $this->block_size) bytes are added, each of which is equal to
     * chr($this->block_size - (strlen($text) % $this->block_size)
     *
     * If padding is disabled and $text is not a multiple of the blocksize, the string will be padded regardless
     * and padding will, hence forth, be enabled.
     *
     * @param string $text
     *
     * @return string
     */
    private function pad(string $text)
    {
        $length = strlen($text);

        if (!$this->padding) {
            if ($length % $this->block_size == 0) {
                return $text;
            } else {
                throw new LogicException(
                    "The plaintext's length ($length) is not a multiple of the block size ({$this->block_size})"
                );
            }
        }

        $pad = $this->block_size - ($length % $this->block_size);

        return str_pad($text, $length + $pad, chr($pad));
    }

    public function decrypt($ciphertext)
    {
        if ($this->paddable) {
            // we pad with chr(0) since that's what mcrypt_generic does.  to quote from
            // {@link http://www.php.net/function.mcrypt-generic}: "The data is padded with "\0"
            // to make sure the length of the data is n * blocksize."
            $ciphertext = str_pad(
                $ciphertext,
                strlen($ciphertext) + ($this->block_size - strlen($ciphertext) % $this->block_size) % $this->block_size,
                chr(0)
            );
        }

        if ($this->changed) {
            $this->clearBuffers();
            $this->changed = false;
        }

        if (!defined('OPENSSL_RAW_DATA')) {
            /** @var string|false */
            $substr = substr($ciphertext, -$this->block_size);
            if (false === $substr) {
                throw new LogicException('Substr failed.');
            }
            $padding = str_repeat(chr($this->block_size), $this->block_size) ^ $substr;

            if (!($encrypted = openssl_encrypt(
                $padding,
                $this->cipherNameOpensslEcb,
                $this->key,
                $this->opensslOptions
            ))) {
                throw new LogicException('Encryption failed.');
            }
            $ciphertext .= substr(
                $encrypted,
                0,
                $this->block_size
            );
        }
        if (!($plaintext = openssl_decrypt(
            $ciphertext,
            $this->cipherNameOpenssl,
            $this->key,
            $this->opensslOptions,
            $this->decryptIV
        ))) {
            throw new LogicException('Decryption failed.');
        }

        return $this->paddable ? $this->unpad($plaintext) : $plaintext;
    }

    /**
     * Sets the engine as appropriate
     *
     * @return void
     */
    private function setEngine()
    {
        $this->engine = null;

        $engine = self::ENGINE_OPENSSL;

        if ($this->isValidEngine($engine)) {
            $this->engine = $engine;
        }

        $this->changed = true;
    }

    public function setOpenSSLOptions($options): void
    {
        $this->opensslOptions = $options;
    }

    /**
     * Clears internal buffers
     *
     * Clearing/resetting the internal buffers is done everytime
     * after disableContinuousBuffer() or on cipher $engine (re)init
     * ie after setKey() or setIV()
     *
     * @return void
     */
    private function clearBuffers()
    {
        // mcrypt's handling of invalid's $iv:
        if (null === $this->iv) {
            $substr = '';
        } else {
            $substr = substr($this->iv, 0, $this->block_size);
        }
        $this->encryptIV = $this->decryptIV = str_pad($substr, $this->block_size, "\0");

        $this->key = str_pad(substr($this->key, 0, $this->key_length), $this->key_length, "\0");
    }

    /**
     * Test for engine validity
     *
     * @param int $engine
     *
     * @return bool
     */
    private function isValidEngine(int $engine)
    {
        if (empty($engine)) {
            return false;
        }

        switch ($engine) {
            case self::ENGINE_OPENSSL:
                if ($this->block_size != 16) {
                    return false;
                }
                $this->cipherNameOpensslEcb = 'aes-' . ($this->key_length << 3) . '-ecb';
                $this->cipherNameOpenssl = 'aes-' . ($this->key_length << 3) . '-' . $this->opensslTranslateMode();
                break;
            default:
                throw new LogicException('Unhandled engine.');
        }

        // prior to PHP 5.4.0 OPENSSL_RAW_DATA and OPENSSL_ZERO_PADDING were not defined.
        // instead of expecting an integer $options openssl_encrypt expected a boolean $raw_data.
        if (!defined('OPENSSL_RAW_DATA')) {
            $this->opensslOptions = true;
        } else {
            $this->opensslOptions = OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING;
        }

        $methods = openssl_get_cipher_methods();
        if (in_array($this->cipherNameOpenssl, $methods)) {
            return true;
        }
        return false;
    }

    /**
     * Unpads a string.
     *
     * If padding is enabled and the reported padding length is invalid the encryption key will be assumed to be wrong
     * and false will be returned.
     *
     * @param string $text
     *
     * @return string
     */
    private function unpad(string $text)
    {
        if (!$this->padding) {
            return $text;
        }

        $length = ord($text[strlen($text) - 1]);

        if (!$length || $length > $this->block_size) {
            throw new LogicException('Length incorrect.');
        }

        return substr($text, 0, -$length);
    }

    /**
     * OpenSSL Mode Mapper
     *
     * May need to be overwritten by classes extending this one in some cases
     *
     * @return string
     */
    private function opensslTranslateMode()
    {
        return 'cbc';
    }
}

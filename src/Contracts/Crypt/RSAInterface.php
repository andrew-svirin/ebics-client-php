<?php

namespace AndrewSvirin\Ebics\Contracts\Crypt;

use AndrewSvirin\Ebics\Models\Crypt\RSA;

/**
 * Crypt RSA representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
interface RSAInterface
{

    /**
     * @return BigIntegerInterface
     */
    public function getExponent(): BigIntegerInterface;

    /**
     * @return BigIntegerInterface
     */
    public function getModulus(): BigIntegerInterface;

    /**
     * Defines the public key
     *
     * Some private key formats define the public exponent and some don't.
     * Those that don't define it are problematic when used in certain contexts.
     * For example, in SSH-2, RSA authentication works by sending the public key along with a
     * message signed by the private key to the server.  The SSH-2 server looks the public
     * key up in an index of public keys and if it's present then proceeds to verify the signature.
     * Problem is, if your private key doesn't include the public exponent this won't work unless
     * you manually add the public exponent. Lib tries to guess if the key being used is the public
     * key but in the event that it guesses incorrectly you might still want to explicitly set the
     * key as being public.
     *
     * Do note that when a new key is loaded the index will be cleared.
     *
     * Returns true on success, false on failure
     *
     * @param string|false $key optional
     *
     * @return bool
     */
    public function setPublicKey($key = false);

    /**
     * Sets the password
     *
     * Private keys can be encrypted with a password. To unset the password, pass in the empty string or false.
     * Or rather, pass in $password such that empty($password) && !is_string($password) is true.
     *
     * @param string|null $password
     *
     * @return void
     */
    public function setPassword($password = null);

    /**
     * Loads a public or private key
     *
     * Returns true on success and false on failure (ie. an incorrect password was provided or the key was malformed)
     *
     * @param string|array $key
     * @param false|int $type optional
     *
     * @return bool
     */
    public function loadKey($key, $type = false);

    /**
     * Decryption
     *
     * @param string $ciphertext
     *
     * @return string
     */
    public function decrypt(string $ciphertext);

    /**
     * Encryption
     *
     * Both self::ENCRYPTION_OAEP and self::ENCRYPTION_PKCS1 both place limits on how long $plaintext can be.
     * If $plaintext exceeds those limits it will be broken up so that it does and the resultant ciphertext's will
     * be concatenated together.
     *
     * @param string $plaintext
     *
     * @return string
     */
    public function encrypt(string $plaintext);

    /**
     * Determines the public key format.
     *
     * @param int $format
     *
     * @return void
     */
    public function setPublicKeyFormat(int $format);

    /**
     * Determines the private key format.
     *
     * @param int $format
     *
     * @return void
     */
    public function setPrivateKeyFormat(int $format);

    /**
     * Determines which hashing function should be used.
     *
     * Used with signature production/verification and (if the encryption mode is self::ENCRYPTION_OAEP)
     * encryption and decryption.
     *
     * @param string $hash = 'sha256'
     *
     * @return void
     */
    public function setHash(string $hash);

    /**
     * Create public / private key pair.
     *
     * Returns an array with the following three elements:
     *  - 'privatekey': The private key.
     *  - 'publickey':  The public key.
     *  - 'partialkey': A partially computed key (if the execution time exceeded $timeout).
     *                  Will need to be passed back to RSA::createKey() as the third parameter
     *                  for further processing.
     *
     * @param int $bits
     * @param int|false $timeout
     * @param array $partial
     *
     * @return array = [
     *   'privatekey' => '<string>',
     *   'publickey' => '<string>',
     *   'partialkey' => '<bool>',
     * ]
     */
    public function createKey($bits = 1024, $timeout = false, $partial = array());

    /**
     * Returns the public key
     *
     * The public key is only returned under two circumstances - if the private key
     * had the public key embedded within it or if the public key was set via setPublicKey().
     * If the currently loaded key is supposed to be the public key this function won't return
     * it since this library, for the most part, doesn't distinguish between public and private keys.
     *
     * @param int $type optional
     *
     * @return string|null
     */
    public function getPublicKey($type = RSA::PUBLIC_FORMAT_PKCS8);

    /**
     * Returns the private key
     *
     * The private key is only returned if the currently loaded key contains the constituent prime numbers.
     *
     * @param int $type optional
     *
     * @return mixed
     */
    public function getPrivateKey($type = RSA::PUBLIC_FORMAT_PKCS1);

    /**
     * Create a signature
     *
     * @param string $message
     *
     * @return string|null
     */
    public function sign(string $message);
}

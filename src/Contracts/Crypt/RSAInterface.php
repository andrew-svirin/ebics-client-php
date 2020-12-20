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

    public function getExponent(): BigIntegerInterface;

    public function getModulus(): BigIntegerInterface;

    /**
     * @param string|false $key
     * @param int|false $type
     *
     * @return mixed
     * @see \phpseclib\Crypt\RSA::setPublicKey()
     */
    public function setPublicKey($key = false, $type = false);

    /**
     * @param string|false $password
     *
     * @return void
     * @see \phpseclib\Crypt\RSA::setPassword()
     */
    public function setPassword($password = false);

    /**
     * @param string|RSAInterface|array $key
     * @param bool|int $type optional
     *
     * @return bool
     * @see \phpseclib\Crypt\RSA::loadKey()
     */
    public function loadKey($key, $type = false);

    /**
     * @param int $mode
     *
     * @return void
     * @see \phpseclib\Crypt\RSA::setEncryptionMode()
     */
    public function setEncryptionMode($mode);

    /**
     * @param string $ciphertext
     *
     * @return string
     * @see \phpseclib\Crypt\RSA::decrypt()
     */
    public function decrypt($ciphertext);

    /**
     * @param string $plaintext
     *
     * @return string
     * @see \phpseclib\Crypt\RSA::encrypt()
     */
    public function encrypt($plaintext);

    /**
     * @param int $format
     *
     * @return void
     * @see \phpseclib\Crypt\RSA::setPublicKeyFormat()
     */
    public function setPublicKeyFormat($format);

    /**
     * @param int $format
     *
     * @return void
     * @see \phpseclib\Crypt\RSA::setPrivateKeyFormat()
     */
    public function setPrivateKeyFormat($format);

    /**
     * @param string $hash
     *
     * @return void
     * @see \phpseclib\Crypt\RSA::setHash()
     */
    public function setHash($hash);

    /**
     * @param string $hash
     *
     * @return void
     * @see \phpseclib\Crypt\RSA::setMGFHash()
     */
    public function setMGFHash($hash);

    /**
     * @param int $bits
     * @param int|false $timeout
     * @param array $partial
     *
     * @return array = [
     *   'privatekey' => '<string>',
     *   'publickey' => '<string>',
     *   'partialkey' => '<bool>',
     * ]
     * @see \phpseclib\Crypt\RSA::createKey()
     */
    public function createKey($bits = 1024, $timeout = false, $partial = array());

    /**
     * @param int $type optional
     *
     * @return string
     * @see \phpseclib\Crypt\RSA::getPublicKey()
     */
    public function getPublicKey($type = RSA::PUBLIC_FORMAT_PKCS8);

    /**
     * @param int $type optional
     *
     * @return string
     * @see \phpseclib\Crypt\RSA::getPrivateKey()
     */
    public function getPrivateKey($type = RSA::PUBLIC_FORMAT_PKCS1);
}

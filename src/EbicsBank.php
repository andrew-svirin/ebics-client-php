<?php

namespace Ukrinsoft\Ebics;

use Ukrinsoft\Ebics\EbicsKeyRing;
use phpseclib\Crypt\RSA;

/**
 * EBICS bank representation.
 */
class EbicsBank
{

    /**
     * An EbicsKeyRing instance.
     * @var EbicsKeyRing 
     */
    private $_keyring;

    /**
     * The HostID of the bank.
     * @var string 
     */
    private $_hostId;

    /**
     * The URL of the EBICS server.
     * @var string 
     */
    private $_url;

    /**
     * Constructor.
     *
     * @param EbicsKeyRing $keyring
     * @param string $hostId
     * @param string $url
     */
    public function __construct(EbicsKeyRing $keyring, $hostId, $url)
    {
        $this->_keyring = $keyring;
        $this->_hostId = (string) $hostId;
        $this->_url = (string) $url;
    }

    /**
     * Getter for {keyring}.
     * @return EbicsKeyRing
     */
    public function getKeyring()
    {
        return $this->_keyring;
    }

    /**
     * Getter for {authenticationKey}.
     * @return string
     */
    public function getAuthenticationKey()
    {
        $keys = $this->_keyring->getKeys();

        return EbicsKeyRing::formatKey($keys["@{$this->_hostId}"]['X']['key'], 'PUBLIC');
    }

    /**
     * Authentication {publicDigest}.
     * @return string
     */
    public function getBankAuthenticationPublicDigest()
    {
        $publicKey = $this->getAuthenticationKey();
        $rsa = new RSA();
        $rsa->setPublicKey($publicKey);

        return EbicsKeyRing::calculatePublicDigest($rsa->exponent->toHex(), $rsa->modulus->toHex());
    }

    /**
     * Getter for bankAuthenticationKeyVersion}.
     * @return string
     */
    public function getBankAuthenticationKeyVersion()
    {
        $keys = $this->_keyring->getKeys();
        return $keys["@{$this->_hostId}"]['X']['version'];
    }

    /**
     * Encryption {publicDigest}.
     * @return string
     */
    public function getBankEncryptionPublicDigest()
    {
        $publicKey = $this->getEncryptionKey();
        $rsa = new RSA();
        $rsa->setPublicKey($publicKey);

        return EbicsKeyRing::calculatePublicDigest($rsa->exponent->toHex(), $rsa->modulus->toHex());
    }

    /**
     * Getter for {encryptionKey}.
     * @return string
     */
    public function getEncryptionKey()
    {
        $keys = $this->_keyring->getKeys();

        return EbicsKeyRing::formatKey($keys["@{$this->_hostId}"]['E']['key'], 'PUBLIC');
    }

    /**
     * Getter for {bankEncryptionKeyVersion}.
     * @return string
     */
    public function getBankEncryptionKeyVersion()
    {
        $keys = $this->_keyring->getKeys();

        return $keys["@{$this->_hostId}"]['E']['version'];
    }

    /**
     * Getter for {hostId}.
     * @return string
     */
    public function getHostId()
    {
        return $this->_hostId;
    }

    /**
     * Getter for {url}.
     * @return string
     */
    public function getUrl()
    {
        return $this->_url;
    }

}

<?php

namespace AndrewSvirin\Ebics;

use AndrewSvirin\Ebics\EbicsKeyRing;

/**
 * EBICS user representation.
 */
class EbicsUser
{

    /**
     * An EbicsKeyRing instance.
     * @var EbicsKeyRing 
     */
    private $_keyring;

    /**
     * The assigned PartnerID (Kunden-ID).
     * @var string 
     */
    private $_partnerId;

    /**
     * The assigned UserID (Teilnehmer-ID).
     * @var string 
     */
    private $_userId;

    /**
     * Constructor.
     *
     * @param EbicsKeyRing $keyring
     * @param string $partnerId
     * @param string $userId
     */
    public function __construct(EbicsKeyRing $keyring, $partnerId, $userId)
    {
        $this->_keyring = $keyring;
        $this->_partnerId = (string) $partnerId;
        $this->_userId = (string) $userId;
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
     * Getter for {partnerId}.
     * @return string
     */
    public function getPartnerId()
    {
        return $this->_partnerId;
    }

    /**
     * Getter for {userId}.
     * @return string
     */
    public function getUserId()
    {
        return $this->_userId;
    }

    /**
     * Getter for {encriptionKey}.
     * @return string base64
     */
    public function getEncriptionKey()
    {
        $keys = $this->_keyring->getKeys();

        return EbicsKeyRing::formatKey($keys['#USER']['E']['pk'], 'PRIVATE', $keys['#USER']['E']['pk_iv']);
    }

    /**
     * Getter for {authorizationKey}.
     * @return string base64
     */
    public function getAuthorizationKey()
    {
        $keys = $this->_keyring->getKeys();

        return EbicsKeyRing::formatKey($keys['#USER']['X']['pk'], 'PRIVATE', $keys['#USER']['X']['pk_iv']);
    }

}

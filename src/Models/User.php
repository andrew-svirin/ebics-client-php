<?php

namespace AndrewSvirin\Ebics\Models;

/**
 * EBICS user representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class User
{
    /**
     * The assigned PartnerID (Kunden-ID).
     *
     * @var string
     */
    private $partnerId;

    /**
     * The assigned UserID (Teilnehmer-ID).
     *
     * @var string
     */
    private $userId;

    /**
     * Constructor.
     *
     * @param string $partnerId
     * @param string $userId
     */
    public function __construct($partnerId, $userId)
    {
        $this->partnerId = (string)$partnerId;
        $this->userId = (string)$userId;
    }

    /**
     * Getter for {partnerId}.
     *
     * @return string
     */
    public function getPartnerId()
    {
        return $this->partnerId;
    }

    /**
     * Getter for {userId}.
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }
}

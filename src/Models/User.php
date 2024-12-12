<?php

namespace EbicsApi\Ebics\Models;

/**
 * EBICS user representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class User
{
    /**
     * The assigned PartnerID (Kunden-ID).
     */
    private string $partnerId;

    /**
     * The assigned UserID (Teilnehmer-ID).
     */
    private string $userId;

    /**
     * Constructor.
     *
     * @param string $partnerId
     * @param string $userId
     */
    public function __construct(string $partnerId, string $userId)
    {
        $this->partnerId = $partnerId;
        $this->userId = $userId;
    }

    /**
     * Getter for {partnerId}.
     *
     * @return string
     */
    public function getPartnerId(): string
    {
        return $this->partnerId;
    }

    /**
     * Getter for {userId}.
     *
     * @return string
     */
    public function getUserId(): string
    {
        return $this->userId;
    }
}

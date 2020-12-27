<?php

namespace AndrewSvirin\Ebics\Models;

/**
 * EBICS bank representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class Bank
{
    /**
     * The HostID of the bank.
     *
     * @var string
     */
    private $hostId;

    /**
     * The URL of the EBICS server.
     *
     * @var string
     */
    private $url;

    /**
     * @var bool
     */
    private $isCertified;

    /**
     * Constructor.
     *
     * @param string $hostId
     * @param string $url
     * @param bool $isCertified
     */
    public function __construct(string $hostId, string $url, bool $isCertified)
    {
        $this->hostId = (string)$hostId;
        $this->url = (string)$url;
        $this->isCertified = $isCertified;
    }

    /**
     * Getter for {hostId}.
     *
     * @return string
     */
    public function getHostId()
    {
        return $this->hostId;
    }

    /**
     * Getter for {url}.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return bool
     */
    public function isCertified(): bool
    {
        return $this->isCertified;
    }
}

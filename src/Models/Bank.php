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
     * @var bool|null
     */
    private $isCertified;

    /**
     * The Server Name of the bank.
     *
     * @var string|null
     */
    private $serverName;

    /**
     * Constructor.
     *
     * @param string $hostId
     * @param string $url
     */
    public function __construct(string $hostId, string $url)
    {
        $this->hostId = $hostId;
        $this->url = $url;
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
     * @param bool $isCertified
     */
    public function setIsCertified(bool $isCertified): void
    {
        $this->isCertified = $isCertified;
    }

    /**
     * @return bool
     */
    public function getIsCertified(): bool
    {
        return (bool)$this->isCertified;
    }

    /**
     * @param string|null $serverName
     */
    public function setServerName(?string $serverName): void
    {
        $this->serverName = $serverName;
    }

    /**
     * @return string|null
     */
    public function getServerName(): ?string
    {
        return $this->serverName;
    }
}

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

    public function __construct(string $hostId, string $url, bool $isCertified)
    {
        $this->hostId = $hostId;
        $this->url = $url;
        $this->isCertified = $isCertified;
    }

    public function getHostId(): string
    {
        return $this->hostId;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function isCertified(): bool
    {
        return $this->isCertified;
    }
}

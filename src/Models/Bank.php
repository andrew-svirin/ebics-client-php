<?php

namespace AndrewSvirin\Ebics\Models;

/**
 * EBICS bank representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class Bank
{
    /**
     * The HostID of the bank.
     */
    private string $hostId;

    /**
     * The URL of the EBICS server.
     */
    private string $url;

    /**
     * Uses certificate.
     *
     * @var bool
     */
    private bool $isCertified = false;

    /**
     * Uses upload orders with ES.
     *
     * @var bool
     */
    private bool $usesUploadWithES = true;

    /**
     * The Server Name of the bank.
     */
    private ?string $serverName = null;

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
    public function getHostId(): string
    {
        return $this->hostId;
    }

    /**
     * Getter for {url}.
     *
     * @return string
     */
    public function getUrl(): string
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
    public function isCertified(): bool
    {
        return $this->isCertified;
    }

    /**
     * @param bool $usesUploadWithES
     *
     * @return void
     */
    public function setUsesUploadWithES(bool $usesUploadWithES): void
    {
        $this->usesUploadWithES = $usesUploadWithES;
    }

    /**
     * @return bool
     */
    public function usesUploadWithES(): bool
    {
        return $this->usesUploadWithES;
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

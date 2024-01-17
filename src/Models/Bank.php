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
    public const VERSION_24 = 'VERSION_24';
    public const VERSION_25 = 'VERSION_25';
    public const VERSION_30 = 'VERSION_30';

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
     * The Server Version of the bank.
     */
    private string $version;

    /**
     * Constructor.
     *
     * @param string $hostId
     * @param string $url
     * @param string $version
     */
    public function __construct(string $hostId, string $url, string $version = self::VERSION_25)
    {
        $this->hostId = $hostId;
        $this->url = $url;
        $this->version = $version;
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

    /**
     * @return string|null
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }
}

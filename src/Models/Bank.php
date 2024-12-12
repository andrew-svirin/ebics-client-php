<?php

namespace EbicsApi\Ebics\Models;

/**
 * EBICS bank representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class Bank
{
    public const COUNTRY_CODE_EU = 'EU';
    public const COUNTRY_CODE_AT = 'AT';
    public const COUNTRY_CODE_DE = 'DE';
    public const COUNTRY_CODE_FR = 'FR';
    public const COUNTRY_CODE_CH = 'CH';

    /**
     * The HostID of the bank.
     */
    private string $hostId;

    /**
     * The URL of the EBICS server.
     */
    private string $url;

    /**
     * The country code from supported list.
     */
    private ?string $countryCode = self::COUNTRY_CODE_EU;

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

    public function getHostId(): string
    {
        return $this->hostId;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setServerName(?string $serverName): void
    {
        $this->serverName = $serverName;
    }

    public function getServerName(): ?string
    {
        return $this->serverName;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(?string $countryCode): void
    {
        $this->countryCode = $countryCode;
    }
}

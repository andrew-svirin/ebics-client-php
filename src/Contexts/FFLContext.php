<?php

namespace AndrewSvirin\Ebics\Contexts;

use AndrewSvirin\Ebics\Contracts\EbicsClientInterface;

/**
 * Business transactions & formats.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
abstract class FFLContext
{
    private string $fileFormat;
    private array $parameters = [];
    /**
     * Country code (ISO 3166-1 alpha-2) (max 2 char)
     * @var string
     */
    private string $countryCode = EbicsClientInterface::COUNTRY_CODE_EU;

    public function setFileFormat(string $fileFormat): self
    {
        $this->fileFormat = $fileFormat;

        return $this;
    }

    public function getFileFormat(): string
    {
        return $this->fileFormat;
    }

    public function setParameter(string $name, string $value): self
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setCountryCode(string $countryCode): self
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }
}

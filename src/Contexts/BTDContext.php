<?php

namespace EbicsApi\Ebics\Contexts;

use EbicsApi\Ebics\Contracts\EbicsClientInterface;

/**
 * Class BTFContext context container for BTD orders - requires EBICS 3.0
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class BTDContext extends BTFContext
{
    private ?string $containerType = null;
    private string $parserFormat = EbicsClientInterface::FILE_PARSER_FORMAT_TEXT;

    public function setContainerType(string $containerType): self
    {
        $this->containerType = $containerType;

        return $this;
    }

    public function getContainerType(): ?string
    {
        return $this->containerType;
    }

    public function setParserFormat(string $parserFormat): self
    {
        $this->parserFormat = $parserFormat;

        return $this;
    }

    public function getParserFormat(): string
    {
        return $this->parserFormat;
    }
}

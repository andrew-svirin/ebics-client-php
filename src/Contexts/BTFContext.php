<?php

namespace AndrewSvirin\Ebics\Contexts;

/**
 * Business transactions & formats.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Geoffroy de Corbiac
 */
abstract class BTFContext extends ServiceContext
{
    private ?string $containerFlag = null;
    private ?string $msgNameVariant = null;
    private ?string $msgNameVersion = null;
    private ?string $msgNameFormat = null;

    public function setContainerFlag(string $containerFlag): self
    {
        $this->containerFlag = $containerFlag;

        return $this;
    }

    public function getContainerFlag(): ?string
    {
        return $this->containerFlag;
    }

    public function setMsgNameVariant(string $msgNameVariant): self
    {
        $this->msgNameVariant = $msgNameVariant;

        return $this;
    }

    public function getMsgNameVariant(): ?string
    {
        return $this->msgNameVariant;
    }

    public function setMsgNameVersion(?string $msgNameVersion): self
    {
        $this->msgNameVersion = $msgNameVersion;

        return $this;
    }

    public function getMsgNameVersion(): ?string
    {
        return $this->msgNameVersion;
    }

    public function setMsgNameFormat(string $msgNameFormat): self
    {
        $this->msgNameFormat = $msgNameFormat;

        return $this;
    }

    public function getMsgNameFormat(): ?string
    {
        return $this->msgNameFormat;
    }
}

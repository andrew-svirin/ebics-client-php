<?php

namespace AndrewSvirin\Ebics\Contexts;

/**
 * Class BTFContext context container for BTD orders - requires EBICS 3.0
 * 
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class BTFContext
{
    /**
     * @var string
     */
    private $serviceName;

    /**
     * @var string
     */
    private $msgName;

    /**
     * @var string
     */
    private $scope;

    /**
     * @var string
     */
    private $serviceOption;

    /**
     * @var string
     */
    private $containerFlag;

    /**
     * @var string
     */
    private $msgNameVariant;

    /**
     * @var string
     */
    private $msgNameVersion;

    /**
     * @var string
     */
    private $msgNameFormat;

    public function setServiceName(string $serviceName): BTFContext
    {
        $this->serviceName = $serviceName;

        return $this;
    }

    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    public function setMsgName(string $msgName): BTFContext
    {
        $this->msgName = $msgName;

        return $this;
    }

    public function getMsgName(): string
    {
        return $this->msgName;
    }

    public function setScope(string $scope): BTFContext
    {
        $this->scope = $scope;

        return $this;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function setServiceOption(string $serviceOption): BTFContext
    {
        $this->serviceOption = $serviceOption;

        return $this;
    }

    public function getServiceOption(): ?string
    {
        return $this->serviceOption;
    }

    public function setContainerFlag(string $containerFlag): BTFContext
    {
        $this->containerFlag = $containerFlag;

        return $this;
    }

    public function getContainerFlag(): ?string
    {
        return $this->containerFlag;
    }

    public function setMsgNameVariant(string $msgNameVariant): BTFContext
    {
        $this->msgNameVariant = $msgNameVariant;

        return $this;
    }

    public function getMsgNameVariant(): ?string
    {
        return $this->msgNameVariant;
    }

    public function setMsgNameVersion(string $msgNameVersion): BTFContext
    {
        $this->msgNameVersion = $msgNameVersion;

        return $this;
    }

    public function getMsgNameVersion(): ?string
    {
        return $this->msgNameVersion;
    }

    public function setMsgNameFormat(string $msgNameFormat): BTFContext
    {
        $this->msgNameFormat = $msgNameFormat;

        return $this;
    }

    public function getMsgNameFormat(): ?string
    {
        return $this->msgNameFormat;
    }

}

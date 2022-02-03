<?php

namespace AndrewSvirin\Ebics\Contexts;

/**
 * General service context.
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
abstract class ServiceContext
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

    public function setServiceName(string $serviceName): ServiceContext
    {
        $this->serviceName = $serviceName;

        return $this;
    }

    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    public function setMsgName(string $msgName): ServiceContext
    {
        $this->msgName = $msgName;

        return $this;
    }

    public function getMsgName(): string
    {
        return $this->msgName;
    }

    public function setScope(string $scope): ServiceContext
    {
        $this->scope = $scope;

        return $this;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function setServiceOption(string $serviceOption): ServiceContext
    {
        $this->serviceOption = $serviceOption;

        return $this;
    }

    public function getServiceOption(): ?string
    {
        return $this->serviceOption;
    }
}

<?php

namespace AndrewSvirin\Ebics\Builders\Request;

use AndrewSvirin\Ebics\Models\Http\Request;
use Closure;

/**
 * Class RequestBuilder builder for model @see \AndrewSvirin\Ebics\Models\Http\Request
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class RequestBuilder
{
    /**
     * @var Request|null
     */
    private $instance;

    /**
     * @var XmlBuilder
     */
    private $xmlBuilder;

    public function createInstance(Closure $callback): RequestBuilder
    {
        $this->instance = new Request();

        $this->xmlBuilder = call_user_func($callback, $this->instance);

        return $this;
    }

    public function addContainerUnsecured(Closure $callback): RequestBuilder
    {
        $this->instance->appendChild($this->xmlBuilder->createUnsecured()->getInstance());

        call_user_func($callback, $this->xmlBuilder);

        return $this;
    }

    public function addContainerSecuredNoPubKeyDigests(Closure $callback): RequestBuilder
    {
        $this->instance->appendChild($this->xmlBuilder->createSecuredNoPubKeyDigests()->getInstance());

        call_user_func($callback, $this->xmlBuilder);

        return $this;
    }

    public function addContainerSecured(Closure $callback): RequestBuilder
    {
        $this->instance->appendChild($this->xmlBuilder->createSecured()->getInstance());

        call_user_func($callback, $this->xmlBuilder);

        return $this;
    }

    public function addContainerHEV(Closure $callback): RequestBuilder
    {
        $this->instance->appendChild($this->xmlBuilder->createHEV()->getInstance());

        call_user_func($callback, $this->xmlBuilder);

        return $this;
    }

    public function popInstance(): Request
    {
        $instance = $this->instance;
        $this->instance = null;

        return $instance;
    }
}

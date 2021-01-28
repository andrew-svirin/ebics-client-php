<?php

namespace AndrewSvirin\Ebics\Builders;

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

    public function createInstance(): RequestBuilder
    {
        $this->instance = new Request();

        return $this;
    }

    public function addContainerUnsecured(Closure $callback): RequestBuilder
    {
        $xmlBuilder = new XmlBuilder($this->instance);
        $this->instance->appendChild($xmlBuilder->createUnsecured()->getInstance());

        call_user_func($callback, $xmlBuilder);

        return $this;
    }

    public function addContainerSecuredNoPubKeyDigests(Closure $callback): RequestBuilder
    {
        $xmlBuilder = new XmlBuilder($this->instance);
        $this->instance->appendChild($xmlBuilder->createSecuredNoPubKeyDigests()->getInstance());

        call_user_func($callback, $xmlBuilder);

        return $this;
    }

    public function addContainerSecured(Closure $callback): RequestBuilder
    {
        $xmlBuilder = new XmlBuilder($this->instance);
        $this->instance->appendChild($xmlBuilder->createSecured()->getInstance());

        call_user_func($callback, $xmlBuilder);

        return $this;
    }

    public function addContainerHEV(Closure $callback): RequestBuilder
    {
        $xmlBuilder = new XmlBuilder($this->instance);
        $this->instance->appendChild($xmlBuilder->createHEV()->getInstance());

        call_user_func($callback, $xmlBuilder);

        return $this;
    }

    public function popInstance(): Request
    {
        $instance = $this->instance;
        $this->instance = null;

        return $instance;
    }
}

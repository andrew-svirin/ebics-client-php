<?php

namespace AndrewSvirin\Ebics\Builders\Request;

use Closure;
use DOMDocument;
use DOMElement;

/**
 * Class HeaderBuilder builder for request container.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class HeaderBuilder
{

    /**
     * @var DOMElement
     */
    private $instance;


    /**
     * @var DOMDocument
     */
    private $dom;

    public function __construct(DOMDocument $dom = null)
    {
        $this->dom = $dom;
    }

    /**
     * Create body for UnsecuredRequest.
     *
     * @return $this
     */
    public function createInstance(): HeaderBuilder
    {
        $this->instance = $this->dom->createElement('header');
        $this->instance->setAttribute('authenticate', 'true');

        return $this;
    }

    public function addStatic(Closure $callback): HeaderBuilder
    {
        $staticBuilder = new StaticBuilder($this->dom);
        $this->instance->appendChild($staticBuilder->createInstance()->getInstance());

        call_user_func($callback, $staticBuilder);

        return $this;
    }

    public function addMutable(Closure $callable = null): HeaderBuilder
    {
        $mutableBuilder = new MutableBuilder($this->dom);
        $this->instance->appendChild($mutableBuilder->createInstance()->getInstance());

        if (null !== $callable) {
            call_user_func($callable, $mutableBuilder);
        }

        return $this;
    }

    public function getInstance(): DOMElement
    {
        return $this->instance;
    }
}

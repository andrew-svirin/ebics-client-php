<?php

namespace AndrewSvirin\Ebics\Builders\Request;

use Closure;

/**
 * Ebics 3.0 Class StaticBuilder builder for request container.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class StaticBuilderV3 extends StaticBuilder
{
    public function addOrderDetails(Closure $callable = null): StaticBuilder
    {
        $orderDetailsBuilder = new OrderDetailsBuilderV3($this->dom);
        $this->instance->appendChild($orderDetailsBuilder->createInstance()->getInstance());

        call_user_func($callable, $orderDetailsBuilder);

        return $this;
    }
}

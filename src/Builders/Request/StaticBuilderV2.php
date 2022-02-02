<?php

namespace AndrewSvirin\Ebics\Builders\Request;

use Closure;

/**
 * Ebics 2.5 Class StaticBuilder builder for request container.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class StaticBuilderV2 extends StaticBuilder
{
    public function addOrderDetails(Closure $callable = null): StaticBuilder
    {
        $orderDetailsBuilder = new OrderDetailsBuilderV2($this->dom);
        $this->instance->appendChild($orderDetailsBuilder->createInstance()->getInstance());

        call_user_func($callable, $orderDetailsBuilder);

        return $this;
    }
}

<?php

namespace EbicsApi\Ebics\Builders\Request;

use Closure;

/**
 * Ebics 2.5 Class BodyBuilder builder for request container.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class BodyBuilderV2 extends BodyBuilder
{
    public function addDataTransfer(Closure $callback): BodyBuilder
    {
        $dataTransferBuilder = new DataTransferBuilderV2($this->dom);
        $this->instance->appendChild($dataTransferBuilder->createInstance()->getInstance());

        call_user_func($callback, $dataTransferBuilder);

        return $this;
    }
}

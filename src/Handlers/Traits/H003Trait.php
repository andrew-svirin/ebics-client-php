<?php

namespace AndrewSvirin\Ebics\Handlers\Traits;

/**
 * Trait H003Trait settings.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
trait H003Trait
{
    protected function getH00XVersion(): string
    {
        return 'H003';
    }

    protected function getH00XNamespace(): string
    {
        return 'http://www.ebics.org/H003';
    }
}

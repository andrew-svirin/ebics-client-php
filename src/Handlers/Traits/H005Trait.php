<?php

namespace AndrewSvirin\Ebics\Handlers\Traits;

/**
 * Trait H005Trait settings.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
trait H005Trait
{
    protected function getH00XVersion(): string
    {
        return 'H005';
    }

    protected function getH00XNamespace(): string
    {
        return 'urn:org:ebics:H005';
    }
}

<?php

namespace EbicsApi\Ebics\Handlers\Traits;

/**
 * Trait H004Trait settings.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
trait H004Trait
{
    protected function getH00XVersion(): string
    {
        return 'H004';
    }

    protected function getH00XNamespace(): string
    {
        return 'urn:org:ebics:H004';
    }
}

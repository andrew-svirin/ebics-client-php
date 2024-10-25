<?php

namespace AndrewSvirin\Ebics\Contexts;

/**
 * Class FULContext context container for FUL orders
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class FULContext
{
    private array $parameters = [];

    public function setParameter(string $name, string $value): void
    {
        $this->parameters[$name] = $value;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}

<?php

namespace AndrewSvirin\Ebics\Contexts;

/**
 * Class BTFContext context container for BTD orders - requires EBICS 3.0
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class BTDContext extends BTFContext
{
    private ?string $containerType = null;

    public function setContainerType(string $containerType): BTDContext
    {
        $this->containerType = $containerType;

        return $this;
    }

    public function getContainerType(): ?string
    {
        return $this->containerType;
    }
}

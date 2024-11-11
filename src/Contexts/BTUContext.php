<?php

namespace AndrewSvirin\Ebics\Contexts;

/**
 * Class BTUContext context container for BTU orders - requires EBICS 3.0
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class BTUContext extends BTFContext
{
    private string $fileName;

    public function setFileName(string $fileName): BTUContext
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }
}

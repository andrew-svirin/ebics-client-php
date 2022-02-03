<?php

namespace AndrewSvirin\Ebics\Models\Http;

use AndrewSvirin\Ebics\Models\Data;
use LogicException;

/**
 * Class Request represents Request model.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class Request extends Data
{
    public function outputFormatted(): string
    {
        $this->preserveWhiteSpace = false;
        $this->formatOutput = true;

        $xml = $this->saveXML();
        if (false === $xml) {
            throw new LogicException('XML was not saved');
        }

        return $xml;
    }
}

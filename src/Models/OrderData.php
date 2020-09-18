<?php

namespace AndrewSvirin\Ebics\Models;

/**
 * Class OrderData represents OrderData model.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class OrderData extends DOMDocument
{
    public function __construct(string $content = null)
    {
        parent::__construct();

        if ($content) {
            $this->loadXML($content);
        }
    }
}

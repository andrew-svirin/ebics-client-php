<?php

namespace AndrewSvirin\Ebics\Models;

use AndrewSvirin\Ebics\Contracts\DataInterface;
use DOMDocument;

/**
 * Class Data represents Data model.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
abstract class Data extends DOMDocument implements DataInterface
{

    public function __construct()
    {
        parent::__construct('1.0', 'utf-8');
        $this->preserveWhiteSpace = false;
    }

    public function getContent(): string
    {
        $content = (string)$this->saveXML();
        $content = str_replace(
            '<?xml version="1.0" encoding="utf-8"?>',
            "<?xml version='1.0' encoding='utf-8'?>",
            $content
        );
        $content = trim($content);

        return $content;
    }

    public function getFormattedContent(): string
    {
        $this->formatOutput = true;

        return (string)$this->saveXML();
    }
}

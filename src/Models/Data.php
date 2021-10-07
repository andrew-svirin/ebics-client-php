<?php

namespace AndrewSvirin\Ebics\Models;

use AndrewSvirin\Ebics\Contracts\DataInterface;
use DOMDocument;
use DOMElement;

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

    public function ensureUnicode(string &$string): void
    {
        if (!mb_check_encoding($string, 'UTF-8')) {
            $string = utf8_encode($string);
        }
    }

    /**
     * @param array $nodes
     * @return DOMElement|false
     */
    public function createElements(array $nodes)
    {
        $elements = [];
        foreach ($nodes as $node) {
            $element = $this->createElement($node);
            if ($element === false) {
                return false;
            }

            if (!empty($elements)) {
                end($elements)->appendChild($element);
            }

            $elements[] = $element;
        }

        return $elements[0];
    }

    public function getContent(): string
    {
        $content = (string)$this->saveXML();
        $content = str_replace(
            '<?xml version="1.0" encoding="utf-8"?>',
            "<?xml version='1.0' encoding='utf-8'?>",
            $content
        );
        $content = str_replace(["\n", "\r", "\t"], '', $content);
        $content = trim($content);

        return $content;
    }

    public function getFormattedContent(): string
    {
        $this->formatOutput = true;

        return (string)$this->saveXML();
    }
}

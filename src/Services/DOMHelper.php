<?php


namespace AndrewSvirin\Ebics\Services;

use DOMNodeList;

class DOMHelper
{
    /**
     * @param  DOMNodeList|false  $domNodeList
     * @return string
     */
    public static function safeItemValue($domNodeList): string
    {
        if ($domNodeList === false) {
            throw new \RuntimeException('empty set');
        }

        $domNode = $domNodeList->item(0);

        if ($domNode === null) {
            throw new \RuntimeException('index 0 is null');
        }

        return $domNode->nodeValue;
    }
}
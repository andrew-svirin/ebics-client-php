<?php


namespace AndrewSvirin\Ebics\Services;

use DOMNodeList;

class SafeItems
{
    /**
     * @param DOMNodeList|false $domNodeList
     */
    public static function safeItemAcces($domNodeList): string
    {
        if ($domNodeList === false) {
            throw new \RuntimeException('empty set');
        }

        $domNode = $domNodeList->item(0);

        if ($domNode === null) {
            throw new \RuntimeException('no set');
        }

        return $domNode->nodeValue;
    }
}
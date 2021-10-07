<?php

namespace AndrewSvirin\Ebics\Contracts;

/**
 * DataInterface class representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
interface DataInterface
{
    /**
     * @param string $string
     */
    public function ensureUnicode(string &$string): void;

    /**
     * @param array $nodes
     * @return \DOMElement|false
     */
    public function createElements(array $nodes);

    /**
     * Get formatted content.
     *
     * @return string
     */
    public function getContent(): string;

    /**
     * Get formatted content.
     *
     * @return string
     */
    public function getFormattedContent(): string;
}

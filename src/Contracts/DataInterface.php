<?php

namespace EbicsApi\Ebics\Contracts;

/**
 * DataInterface class representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
interface DataInterface
{
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

<?php

namespace AndrewSvirin\Ebics\Contracts;

/**
 * PdfFactory class representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
interface PdfFactoryInterface
{
    /**
     * Create PDF document from HTML.
     */
    public function createFromHtml(string $html): PdfInterface;
}

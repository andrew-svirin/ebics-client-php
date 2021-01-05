<?php

namespace AndrewSvirin\Ebics\Factories;

use AndrewSvirin\Ebics\Contracts\PdfInterface;
use AndrewSvirin\Ebics\Models\Pdf;

/**
 * Class PdfFactory represents producers for the @see \AndrewSvirin\Ebics\Contracts\PdfInterface.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class PdfFactory
{
    public function createFromHtml(string $html): PdfInterface
    {
        $pdf = new Pdf();
        $pdf->writeHtml($html);

        return $pdf;
    }
}

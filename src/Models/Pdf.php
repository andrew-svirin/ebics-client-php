<?php

namespace AndrewSvirin\Ebics\Models;

use AndrewSvirin\Ebics\Contracts\PdfInterface;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

/**
 * EBICS user representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class Pdf extends Mpdf implements PdfInterface
{
    public function outputString(): string
    {
        return $this->Output('', Destination::STRING_RETURN);
    }

    public function outputFile($fileName): void
    {
        $this->Output($fileName, Destination::FILE);
    }
}

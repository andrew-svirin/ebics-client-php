<?php

namespace AndrewSvirin\Ebics\Contracts;

use Mpdf\HTMLParserMode;

/**
 * PDF class representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
interface PdfInterface
{

    /**
     * @param string $html
     * @param int $mode Use HTMLParserMode constants. Controls what parts of the $html code is parsed.
     * @param bool $init Clears and sets buffers to Top level block etc.
     * @param bool $close If false leaves buffers etc. in current state, so that it can continue a block etc.
     *
     * @return void
     *
     * @see \Mpdf\Mpdf::WriteHTML()
     */
    public function writeHTML($html, $mode = HTMLParserMode::DEFAULT_MODE, $init = true, $close = true);

    /**
     * @return string
     *
     * @see \Mpdf\Mpdf::Output('', Destination::STRING_RETURN)
     */
    public function outputString(): string;

    /**
     * @param string $fileName
     *
     * @return void
     *
     * @see \Mpdf\Mpdf::Output('', Destination::FILE)
     */
    public function outputFile(string $fileName): void;
}

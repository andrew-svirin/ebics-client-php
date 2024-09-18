<?php

namespace AndrewSvirin\Ebics\Models;

use FPDF;

/**
 * Pdf wrapper for FPDF.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class Pdf extends FPDF
{

    private int $offsetY = 20;

    /**
     * @var int
     */
    private int $lastY;

    /**
     * @var int
     */
    private int $totalPages = 0;

    /**
     * @var string
     */
    private string $sansFont = 'Arial';
    /**
     * @var string
     */
    private string $monoFont = 'Courier';

    /**
     * @var string
     */
    private string $header;

    /**
     * @inheritDoc
     */
    public function header(): void
    {
        $this->SetFont($this->sansFont, 'B', 12);
        $this->Cell(0, 10, $this->header, 0, 1, 'C');
    }

    /**
     * @inheritDoc
     */
    public function footer(): void
    {
        $this->SetY(-15);
        $this->SetFont($this->sansFont, 'I', 8);
        $this->Cell(0, 10, "{$this->PageNo()}/$this->totalPages", 0, 0, 'C');
    }

    public function setHeader(string $text): void
    {
        $this->header = $text;
    }

    /**
     * @return void
     */
    public function newPage(): void
    {
        $this->AddPage();
        $this->lastY = $this->offsetY;
    }

    /**
     * @param int $number
     *
     * @return void
     */
    public function totalPages(int $number): void
    {
        $this->totalPages = $number;
    }

    /**
     * Header function.
     * @param string $text
     * @param int $size
     * @param int|null $txtColor
     * @param int|null $bgColor
     *
     * @return void
     */
    public function h(string $text, int $size, int $txtColor = null, int $bgColor = null): void
    {
        $this->SetY($this->lastY);
        $this->SetFont($this->sansFont, 'B', $size);

        if (!is_null($txtColor)) {
            $this->SetTextColor($txtColor);
        }

        if (!is_null($bgColor)) {
            $this->SetFillColor($bgColor);
        }

        $this->Cell(0, $size * 0.7, $text, 0, 1, 'L', true);

        $this->lastY += $size + 2;
    }

    /**
     * @param string $text
     * @param string $style
     * @param bool $shift
     * @param int|null $right
     *
     * @return void
     */
    public function c(string $text, string $style = '', bool $shift = true, int $right = null): void
    {
        $this->SetY($this->lastY);
        if ($right) {
            $this->SetX($right);
        }

        $this->SetTextColor(0, 0, 0);
        $this->SetFont($this->sansFont, $style, 9);
        $this->Cell(0, 0, $text);

        if ($shift) {
            $this->lastY += 6;
        }
    }

    public function pre(string $text, bool $shift = true, int $right = null): void
    {
        $this->SetY($this->lastY);
        if ($right) {
            $this->SetX($right);
        }

        $this->SetTextColor(0, 0, 0);
        $this->SetFont($this->monoFont, '', 8);
        $this->Cell(0, 0, $text);

        if ($shift) {
            $this->lastY += 4;
        }
    }

    /**
     * Output text in PDF string.
     */
    public function outputPDF(): string
    {
        return $this->Output('', 'S');
    }
}

<?php
/*
* created on: 03/02/2022 - 20:49
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Classes\PDFGeneration;

abstract class BasePDF
{

    // the current vertical position (of where text will be output next)
    // this is tracked so we can (for example) add "floating text" to the right
    protected $curY;

    protected $curTextSize;

    /** @var MyTCPDF */
    protected $pdf;

    const PAGE_ORIENTATION_PORTRAIT = 'P';
    const PAGE_ORIENTATION_LANDSCAPE = 'L';

    const UNIT_MM = 'mm';
    const UNIT_PT = 'pt';
    const UNIT_INCH = 'in';

    const PAGE_FORMAT = 'A4';

    const OUTPUT_BROWSER_INLINE     = 'I';    // output to the browser
    const OUTPUT_BROWSER_DOWNLOAD   = 'D';    // "send to the browser and force a file download with the name given by name"
    const OUTPUT_FILESYSTEM         = 'F';    // save to a local server file with the name given by name
    // there are more options available

    public function output(string $filepath, $dest = self::OUTPUT_BROWSER_INLINE)
    {
        $this->pdf->Output($filepath, $dest);

    }

    // this provides an easy way for a curious dev to output and view the PDF (instead of having to create their own dummy inputs / data to *simply* see the PDF).
    // it also forces the creation of dummy data so that it's "shape" / data can be reviewed
    abstract function generateViaDummyData();

    // line break
    // note: this just adds the line height to $this->curY
    protected function lb(int $numberOfLineBreaks = 1)
    {
        for($i = 0; $i < $numberOfLineBreaks; $i++) {
            $this->addToCurY($this->curTextSize);
        }

    }

    // shorthand method.
    protected function writeHTML($html, $x = '', $addLineBreak = true)
    {
        $this->pdf->writeHTMLCell(0, 0, $x, $this->curY, $html, 0, 1, 0, true, '', true);
        if ($addLineBreak) {
            $this->lb();
        }

    }

    protected function setTextSize(int $size)
    {
        $this->curTextSize = $size;
        $this->pdf->SetFont('dejavusans', '', $size, '', true);
    }

    // Add a text lines worth of spacing to $curY (including the "lineheight")
    protected function addToCurY(int $size)
    {
        $this->curY = $this->curY + ($size * $this->pdf->getCellHeightRatio());
    }
}
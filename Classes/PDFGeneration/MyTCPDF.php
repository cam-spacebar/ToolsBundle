<?php
/*
* created on: 04/02/2022 - 17:03
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Classes\PDFGeneration;

class MyTCPDF extends \TCPDF {
    // overides header()
    public function Header() {
        // Logo
        $image_file = K_PATH_IMAGES.'logo.png';
//        "/home/cameronrobertburns/php/twenchaLE/vendor/tecnickcom/tcpdf/examples/images/logo.png
//        DUMP(__FILE__);
//        DD($image_file);
//        $image_file = './'
        $image_file = '/home/cameronrobertburns/php/twenchaLE/src/VisageFour/Bundle/ToolsBundle/Files/NTM logo sml (500x500).png';

//        $this->Cell(0, 1, $this->header_title, 1, 1, 'L', 0, '', 0, false, 'M', 'M');
//        $this->Cell(0, 0, $this->header_string, 1, false, 'L', 0, '', 0, false, 'M', 'M');
        $left=80;
        $this->SetFont('helvetica', '', 11);
        $this->writeHTMLCell(0, 0, $left, '', $this->header_title , 0, 1, 0, true, '', true);
        $this->SetFont('helvetica', '', 9);
        $this->writeHTMLCell(0, 0, $left, '', $this->header_string , 0, 1, 0, true, '', true);
        $this->Image($image_file, 40, 10, 32, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}
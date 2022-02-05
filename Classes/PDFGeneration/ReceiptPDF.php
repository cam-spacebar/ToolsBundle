<?php
/*
* created on: 03/02/2022 - 20:48
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Classes\PDFGeneration;

use App\Entity\Purchase\Checkout;
use App\Entity\Purchase\Product;
use App\Entity\Purchase\PurchaseQuantity;
use VisageFour\Bundle\ToolsBundle\Fixtures\PurchaseDummyData;

class ReceiptPDF extends BasePDF
{

    public function __construct()
    {
    }

    public function generateViaDummyData()
    {
        $checkoutDummyData = new PurchaseDummyData();
        $checkout1 = $checkoutDummyData->getOtaCheckoutDummy1();
        $checkout1->setToPaid();

        $this->generate($checkout1);
    }

    public function generate($checkout)
    {
        $this->curY = 0;
        // multiplier needed to convert mm to pts
        $mmToPt = 2.83465;

        // create new PDF document
        $this->pdf = new MyTCPDF(self::PAGE_ORIENTATION_PORTRAIT, self::UNIT_PT, self::PAGE_FORMAT, true, 'UTF-8', false);
        $pdf = $this->pdf;

        // set document information
        $pdf->SetCreator('New To Melbourne PDF Generator');
        $pdf->SetAuthor('New To Melbourne');
        $pdf->SetTitle('Receipt');
        $pdf->SetSubject('Receipt from purchase');
        $pdf->SetKeywords('Receipt');

        // set default header data
        $logo = '../src/VisageFour/Bundle/ToolsBundle/Files/logo.png';
//        dump(is_file($logo));
//        PDF_HEADER_LOGO
//        K_PATH_IMAGES
        $pdf->SetHeaderData($logo, PDF_HEADER_LOGO_WIDTH, 'New to Melbourne - tax invoice', 'ABN: 91 930 593 897', array(0,0,0), array(0,0,0));
        $pdf->setFooterData(array(0,64,0), array(0,64,128));

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT * $mmToPt, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT * $mmToPt);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER * $mmToPt);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER * $mmToPt);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

// ---------------------------------------------------------

        // set default font subsetting mode
        $pdf->setFontSubsetting(true);

        // Set font
        // dejavusans is a UTF-8 Unicode font, if you only need to
        // print standard ASCII chars, you can use core fonts like
        // helvetica or times to reduce file size.

        $pdf->AddPage();

        // set text shadow effect
        $pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));

        $this->curY = 70;
        $this->setTextSize(15);
        $this->writeHTML('<b>Your purchases:</b>');

        $this->outputCheckoutItems($checkout);

        $this->lb();

        $this->writeHTML('<b>Total payment:</b> '. $checkout->getTotalAsString() .' (including GST)');
        $this->writeHTML('(Please note: coupon / discount amounts are not detailed on this receipt.)');
        $this->lb();

        $this->outputOrderDetailsSection($checkout);
    }

    private function outputOrderDetailsSection(Checkout $checkout)
    {
        $person = $checkout->getRelatedPerson();
        $this->writeHTML('<b>Order details:</b> '. $checkout->getId());
        $this->writeHTML('Order no: '. $checkout->getId());
        $this->writeHTML('Ordered by: '. $person->getFullName() .' ('. $person->getEmail() .')');

        $this->writeHTML('Purchase date: '. $checkout->getPaymentDateTimeAsString('d-m-Y'));
        $this->setTextSize(7);
        $this->writeHTML('(Please note: date format is European / Australian: Day-Month-Year)');
    }

    private function outputCheckoutItems (Checkout $checkout) {
        $pdf = $this->pdf;
        /**
         * @var PurchaseQuantity $curQuantity
         */

        foreach ($checkout->getRelatedQuantities() as $curI => $curQuantity) {
            $curProduct = $curQuantity->getRelatedProduct();

            $this->setTextSize(10);
            $this->outputLineItemSubTotal($curQuantity);

            $this->outputProductTitle($curQuantity);
            $this->setTextSize(8);

            // write each line item (for the curProduct
            foreach ($curProduct->getLineItems() as $curI2 => $curLineItem) {
                $this->writeHTML($curLineItem);
            }
            $this->lb();

        }
    }

    private function outputProductTitle(PurchaseQuantity $curQuantity)
    {
        $product = $curQuantity->getRelatedProduct();
        $pluralNoun = ($curQuantity->getQuantity() > 1) ? 's': '';
        $productTitle = $product->getTitle() .' (x'. $curQuantity->getQuantity() .' ticket'. $pluralNoun .')';
        $size = 11;
        $this->pdf->SetFont('dejavusans', '', $size, '', true);
        $this->writeHTML('<b>'. $productTitle .'</b>', '', false);
        $this->addToCurY($size);
    }

    private function outputLineItemSubTotal(PurchaseQuantity $quan)
    {
        $price = $quan->getTotalAsString();
        $this->writeHTML($price, 450, false);
    }
}
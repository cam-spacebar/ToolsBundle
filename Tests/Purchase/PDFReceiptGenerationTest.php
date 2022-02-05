<?php
/*
* created on: 05/02/2022 - 14:35
* by: Cameron
*/

namespace App\VisageFour\Bundle\ToolsBundle\Tests\Purchase;

use VisageFour\Bundle\ToolsBundle\Classes\PDFGeneration\BasePDF;
use VisageFour\Bundle\ToolsBundle\Classes\PDFGeneration\ReceiptPDF;
use VisageFour\Bundle\ToolsBundle\Classes\Testing\CustomKernelTestCase;
use VisageFour\Bundle\ToolsBundle\Fixtures\PurchaseDummyData;

/**
 * Class SecurityTest
 * @package App\Tests\Service
 *
 * === Test Case Documentation: ===
 * Run all tests:
 * - ./vendor/bin/phpunit
 * Run all the tests in this file:
 * - ./vendor/bin/phpunit src/VisageFour/Bundle/ToolsBundle/Tests/Purchase/PDFReceiptGeneration.php
 *
 * Create new test case [P-CB-087]
 * https://docs.google.com/presentation/d/1-AYb7xtRScoWsB3jxHnThsBJVgwY8DzEKLacGVCB28c/edit#slide=id.p
 *
 * (comment version: 1.02)
 */
class PDFReceiptGenerationTest extends CustomKernelTestCase
{

    private function getServices($debuggingOutputOn)
    {
        $container = self::$kernel->getContainer();

//        $this->checkoutRepo = $container->get('test.'. CheckoutRepository::class);
//        $this->checkoutRepo->setOutputValuesOnCreation($debuggingOutputOn);
    }

    protected function customTearDown()
    {

    }

    /**
     * Setup that is specific to this test case.
     * (this runs once prior to each test case / method())
     */
    protected function customSetUp()
    {
        $this->getServices(true);
        return true;
    }

    protected function tearDown(): void
    {
//        $this->outputDebugToTerminal('tearDown()');
//        $this->removeUser($this->person);
//        $this->manager->persist($this->person);
//        $this->manager->flush();
    }

    /**
     * @test
     * ./vendor/bin/phpunit src/VisageFour/Bundle/ToolsBundle/Tests/Purchase/PDFReceiptGenerationTest.php --filter PDFReceiptGeneration
     *
     * Test calculation of totals (with and without a coupon)
     */
    public function PDFReceiptGeneration(): void
    {
        $receipt = new ReceiptPDF();
        $receipt->generateViaDummyData();

        $basename = getcwd() .'/var/PDF receipt test 1.pdf';
        $receipt->output($basename, BasePDF::OUTPUT_FILESYSTEM);

        $this->assertFileExists($basename);
        unlink($basename);

    }

}
<?php
/*
* created on: 29/11/2021 - 13:36
* by: Cameron
*/


namespace VisageFour\Bundle\ToolsBundle\Tests\QRCode;


use App\Entity\UrlShortener\Url;
use VisageFour\Bundle\ToolsBundle\Classes\Testing\CustomKernelTestCase;
use VisageFour\Bundle\ToolsBundle\Services\QRcode\QRCodeGenerator;

/**
 * Class SecurityTest
 * @package App\Tests\Service
 *
 * === Test Case Documentation: ===
 * Run all tests:
 * - ./vendor/bin/phpunit
 * Run all the tests in this file:
 * - ./vendor/bin/phpunit src/VisageFour/Bundle/ToolsBundle/Tests/QRCode/QRCodeGenerationTest.php
 *
 * Create new test case [P-CB-087]
 * https://docs.google.com/presentation/d/1-AYb7xtRScoWsB3jxHnThsBJVgwY8DzEKLacGVCB28c/edit#slide=id.p
 *
 * (comment version: 1.02)
 */
class QRCodeGenerationTest extends CustomKernelTestCase
{

    /** @var QRCodeGenerator */
    private $QRCodeGenerator;

    private function getServices($debuggingOutputOn)
    {
        $container = self::$kernel->getContainer();

        $this->QRCodeGenerator = $container->get('test.'. QRCodeGenerator::class);

        $this->getEntityManager();
    }

    /**
     * Setup that is specific to this test case.
     * (this runs once prior to each test case / method())
     */
    protected function customSetUp()
    {
        $this->getServices(true);

        $this->testingHelper->truncateEntities([
            Url::class
        ]);

        return true;
    }

    protected function customTearDown(): void
    {
//        $this->outputDebugToTerminal('tearDown()');
//        $this->removeUser($this->person);
//        $this->manager->persist($this->person);
//        $this->manager->flush();
    }


    /**
     * @test
     * ./vendor/bin/phpunit src/VisageFour/Bundle/ToolsBundle/Tests/QRCode/QRCodeGenerationTest.php --filter generateQRcodeSimple
     *
     * create a simple QR code
     */
    public function generateQRcodeSimple(): void
    {
//        $outputPathname = __DIR__.'/../../Tests/TestFiles/QRCode/test_output.png';
        $outputPathname = 'var/test_output_QR_code.png';
        $contents = 'Custom QR code contents';
        $this->QRCodeGenerator->generateQRCode($outputPathname, $contents);
        $this->assertFileExists($outputPathname);
        unlink($outputPathname);
    }

    /**
     * @test
     * ./vendor/bin/phpunit src/VisageFour/Bundle/ToolsBundle/Tests/QRCode/QRCodeGenerationTest.php --filter generateShortUrlQRcode
     *
     * Create a QR code that contains a short URL
     */
    public function generateShortUrlQRcode(): void
    {
        $url = 'http://www.NewToMelbourne.org/product2?coupon=334AG';
        $pathname = $this->QRCodeGenerator->generateShortUrlQRCodeFromURL($url);
        $this->assertFileExists($pathname);

        $this->em->flush();

        $this->testingHelper->assertNumberOfDBTableRecords(1, Url::class, $this);

    }
}
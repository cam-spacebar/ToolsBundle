<?php
/*
* created on: 29/11/2021 - 16:45
* by: Cameron
*/

namespace App\VisageFour\Bundle\ToolsBundle\Tests\ImageOverlay;

use VisageFour\Bundle\ToolsBundle\Classes\Testing\CustomKernelTestCase;
use VisageFour\Bundle\ToolsBundle\Services\FileManager\ImageOverlayManager;

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
class ImageOverlayTest extends CustomKernelTestCase
{
    /** @var ImageOverlayManager */
    private $imageOverlayManager;

    private function getServices($debuggingOutputOn)
    {
        $container = self::$kernel->getContainer();

        $this->imageOverlayManager = $container->get('test.'. ImageOverlayManager::class);

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
//            Url::class
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
     * ./vendor/bin/phpunit src/VisageFour/Bundle/ToolsBundle/Tests/ImageOverlay/ImageOverlayTest.php --filter overlayImageSimple
     *
     * create a simple QR code
     */
    public function overlayImageSimple(): void
    {
        $baseDir = 'src/VisageFour/Bundle/ToolsBundle/Tests/TestFiles/ImageOverlay/';
        $this->imageOverlayManager->overlayImage(
            $baseDir. 'FF A4 flyer.png',
            $baseDir. 'QRCode1.png',
            350,
            640,
            0,
            90
        );
        work from here: refactor into service: ImageManipulation
//        $outputPathname = __DIR__.'/../../Tests/TestFiles/QRCode/test_output.png';;
//        $outputPathname = 'var/test_output_QR_code.png';
//        $contents = 'Custom QR code contents';
//        $this->QRCodeGenerator->generateQRCode($outputPathname, $contents);
//        $this->assertFileExists($outputPathname);

    }
}
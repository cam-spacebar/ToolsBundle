<?php
/*
* created on: 29/11/2021 - 16:45
* by: Cameron
*/

namespace App\VisageFour\Bundle\ToolsBundle\Tests\Image;

use VisageFour\Bundle\ToolsBundle\Classes\Testing\CustomKernelTestCase;
use VisageFour\Bundle\ToolsBundle\Services\Image\ImageManipulation;

/**
 * Class SecurityTest
 * @package App\Tests\Service
 *
 * === Test Case Documentation: ===
 * Run all tests:
 * - ./vendor/bin/phpunit
 * Run all the tests in this file:
 * - ./vendor/bin/phpunit src/VisageFour/Bundle/ToolsBundle/Tests/Image/ImageManipulation.php
 *
 * Create new test case [P-CB-087]
 * https://docs.google.com/presentation/d/1-AYb7xtRScoWsB3jxHnThsBJVgwY8DzEKLacGVCB28c/edit#slide=id.p
 *
 * (comment version: 1.02)
 */
class ImageManipulationTest extends CustomKernelTestCase
{
    /** @var ImageManipulation */
    private $imageManipulation;

    private function getServices($debuggingOutputOn)
    {
        $container = self::$kernel->getContainer();

        $this->imageManipulation = $container->get('test.'. ImageManipulation::class);

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
     * ./vendor/bin/phpunit src/VisageFour/Bundle/ToolsBundle/Tests/Image/ImageManipulation.php --filter overlayImageSimple
     *
     * Create a simple QR code
     */
    public function overlayImageSimple(): void
    {
        $baseDir = 'src/VisageFour/Bundle/ToolsBundle/Tests/TestFiles/Image/';
        $compositeImg = $this->imageManipulation->overlayImage (
            $baseDir. 'FF A4 flyer.png',
            $baseDir. 'QRCode1.png',
            350,
            640,
            0,
            90
        );

        $filePath = "var/ImageManipulation/overlayTestResult.png";
        $this->imageManipulation->saveImage($compositeImg, $filePath);

        $this->assertFileExists($filePath);

    }
}
<?php
/*
* created on: 30/11/2021 - 13:28
* by: Cameron
*/


namespace App\VisageFour\Bundle\ToolsBundle\Tests\OverlayTemplate;

use App\VisageFour\Bundle\ToolsBundle\Services\OverlayTemplate\OverlayManager;
use VisageFour\Bundle\ToolsBundle\Classes\Testing\CustomKernelTestCase;
use VisageFour\Bundle\ToolsBundle\Services\FileManager\FileManager;
use VisageFour\Bundle\ToolsBundle\Services\Image\ImageManipulation;

/**
 * Class SecurityTest
 * @package App\Tests\Service
 *
 * === Test Case Documentation: ===
 * Run all tests:
 * - ./vendor/bin/phpunit
 * Run all the tests in this file:
 * - ./vendor/bin/phpunit src/VisageFour/Bundle/ToolsBundle/Tests/Image/ImageOverlayTest.php
 *
 * Create new test case [P-CB-087]
 * https://docs.google.com/presentation/d/1-AYb7xtRScoWsB3jxHnThsBJVgwY8DzEKLacGVCB28c/edit#slide=id.p
 *
 * (comment version: 1.02)
 */
class ImageOverlayTest
{
    /** @var OverlayManager */
    private $overlayManager;

    /** @var FileManager */
    private $fileManager;

    private function getServices($debuggingOutputOn)
    {
        $container = self::$kernel->getContainer();

        $this->overlayManager = $container->get('test.'. OverlayManager::class);
        $this->overlayManager = $container->get('test.'. FileManager::class);

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
     * ./vendor/bin/phpunit src/VisageFour/Bundle/ToolsBundle/Tests/Image/ImageOverlayTest.php --filter overlayImageSimple
     *
     *
     */
    public function overlayImageSimple(): void
    {
        $filepath = 'src/VisageFour/Bundle/ToolsBundle/Tests/TestFiles/Image/FF A4 flyer.png';
        $destinationFolder = 'marketing/posters';
        $imageFile = $this->fileManager->persistFile($filepath, $destinationFolder);

        // create template and overlay
        $this->overlayManager->createNewTemplateSimple(
            $imageFile
        );

//        $baseDir = 'src/VisageFour/Bundle/ToolsBundle/Tests/TestFiles/ImageManipulation/';
//        $compositeImg = $this->imageManipulation->overlayImage (
//            $baseDir. 'FF A4 flyer.png',
//            $baseDir. 'QRCode1.png',
//            350,
//            640,
//            0,
//            90
//        );
//
//        $filePath = "var/ImageManipulation/overlayTestResult.png";
//        $this->imageManipulation->saveImage($compositeImg, $filePath);

    }
}
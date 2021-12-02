<?php
/*
* created on: 30/11/2021 - 13:28
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Tests\Image;

use App\Entity\FileManager\ImageOverlay;
use App\Entity\FileManager\Template;
use App\Entity\UrlShortener\Url;
use VisageFour\Bundle\ToolsBundle\Services\Image\OverlayManager;
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
class ImageOverlayTest extends CustomKernelTestCase
{
    /** @var OverlayManager */
    private $overlayManager;

    /** @var FileManager */
    private $fileManager;

    private function getServices($debuggingOutputOn)
    {
        $container = self::$kernel->getContainer();

        $this->overlayManager   = $container->get('test.'. OverlayManager::class);
        $this->fileManager      = $container->get('test.'. FileManager::class);

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
            Template::class,
            ImageOverlay::class
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
        $template = $this->overlayManager->createNewTemplateAndOverlay(
            $imageFile,
            200,
            100,
            0,
            100,
            'http://www.NewToMelbourne.org/product8?coupon=4422asds'
        );

        $this->em->flush();
        $this->testingHelper->assertNumberOfDBTableRecords(1, Template::class, $this);
        $this->testingHelper->assertNumberOfDBTableRecords(1, ImageOverlay::class, $this);

        Continue from here:
            - finish writing test: create composite?

        // manual testing
        // mysql -u root
        // show databases;
        // use twencha_le_test;
        // show tables;
        // select * from boomerprint_template;
        // select * from boomerprint_overlay;

    }
}
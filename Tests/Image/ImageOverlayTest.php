<?php
/*
* created on: 30/11/2021 - 13:28
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Tests\Image;

use App\Entity\FileManager\File;
use App\Entity\FileManager\ImageOverlay;
use App\Entity\FileManager\Template;
use App\Entity\UrlShortener\Url;
use VisageFour\Bundle\ToolsBundle\Entity\PrintAttribution\TrackedFile;
use VisageFour\Bundle\ToolsBundle\Services\Logging\HybridLogger;
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
            ImageOverlay::class,
            File::class
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
     * Creates the entities needed for tests
     *
     */
    public function createImageFileTemplateAndImageOverlayEntites(): Template
    {
        $filepath = 'src/VisageFour/Bundle/ToolsBundle/Tests/TestFiles/Image';
        $duplicateFilepath = $this->duplicateLocalFile($filepath, 'FF A4 flyer.png');

        $destinationFolder = 'marketing/posters';
        $imageFile = $this->fileManager->persistFile($duplicateFilepath, $destinationFolder);

        // create template and overlay
        $template = $this->overlayManager->createNewTemplateAndOverlay(
            $imageFile,
            200,
            100,
            0,
            100,
            'url'
        );

        $template->setRelatedOriginalFile($imageFile);
        $imageFile->addRelatedTemplate($template);

        $this->em->flush();

        return $template;
    }

    /**
     * @test
     * ./vendor/bin/phpunit src/VisageFour/Bundle/ToolsBundle/Tests/Image/ImageOverlayTest.php --filter deleteImage
     *
     * Test the deletion of File, template and imageOverlay DB entites.
     */
    public function deleteImage(): void
    {
        $template = $this->createImageFileTemplateAndImageOverlayEntites();

        $this->overlayManager->deleteFile($template->getRelatedOriginalFile());

        $this->em->flush();
        $this->testingHelper->assertNumberOfDBTableRecords(0, File::class, $this);
        $this->testingHelper->assertNumberOfDBTableRecords(0, Template::class, $this);
        $this->testingHelper->assertNumberOfDBTableRecords(0, ImageOverlay::class, $this);
    }

    /**
     * @test
     * ./vendor/bin/phpunit src/VisageFour/Bundle/ToolsBundle/Tests/Image/ImageOverlayTest.php --filter produceTrackedFileComposite
     *
     * create a template entity and overlay entity, use them to overlay a poster image with a QR code (of a shortened URL).
     * Then save the resulting composite image file to S3 as a File entity.
     */
    public function produceTrackedFileComposite(): void
    {
        $template = $this->createImageFileTemplateAndImageOverlayEntites();

        $this->em->flush();
        $this->testingHelper->assertNumberOfDBTableRecords(1, Template::class, $this);
        $this->testingHelper->assertNumberOfDBTableRecords(1, ImageOverlay::class, $this);

        $imageFile = $template->getRelatedOriginalFile();
        $payload = array (
            'url'   => 'http://www.NewToMelbourne.org/product8?coupon=4422asds'
        );
        $composite = $this->overlayManager->createCompositeImage($imageFile, $template, $payload);

        // todo: create tracked file.

        // manual testing
//        copy($composite->getLocalFilePath(), 'var/overlayTest.png');
        $this->em->flush();
        $this->testingHelper->assertNumberOfDBTableRecords(2, File::class, $this);

        // cleanup
        $this->overlayManager->deleteFile($template->getRelatedOriginalFile());
        $this->overlayManager->deleteFile($composite);
        $this->em->flush();

        $this->testingHelper->assertNumberOfDBTableRecords(0, File::class, $this);
        $this->testingHelper->assertNumberOfDBTableRecords(0, TrackedFile::class, $this);

        // == manual testing commands ==
        // die('end test prematurely');
        // mysql -u root
        // show databases;
        // use twencha_le_test;
        // show tables;
        // select * from boomerprint_template;
        // select * from boomerprint_overlay;
        // select * from boomerprint_file;
    }

    /**
     * @test
     * ./vendor/bin/phpunit src/VisageFour/Bundle/ToolsBundle/Tests/Image/ImageOverlayTest.php --filter generateBatchOfCompositeFiles
     *
     */
    public function generateBatchOfCompositeFiles(): void
    {
        $template = $this->createImageFileTemplateAndImageOverlayEntites();
        $this->em->flush();

        $imageFile = $template->getRelatedOriginalFile();
        $payload = array (
            'url'   => 'http://www.NewToMelbourne.org/product8?coupon=4422asds'
        );

//        $composite = $this->overlayManager->createCompositeImage($imageFile, $template, $payload);

        $count = 3;
        $batch = $this->overlayManager->createNewBatch($count, $imageFile, $template, $payload);


    }
}
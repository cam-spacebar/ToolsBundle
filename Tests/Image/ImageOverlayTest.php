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
use VisageFour\Bundle\ToolsBundle\Entity\PrintAttribution\Batch;
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
            File::class,
            Batch::class,
            TrackedFile::class
        ]);

        return true;
    }

    protected function customTearDown(): void
    {
//        $this->outputDebugToTerminal('tearDown()');
//        $this->removeUser($this->person);
//        $this->manager->persist($this->person);
//        $this->manager->flush();
//        die( 'attempting to asdasds')   ;
        $this->overlayManager->deleteAllFiles(true, false);
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
     * Test the deletion of a File entity (it's remote, local files and handling of DB record), template and imageOverlay DB entities.
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
     * ./vendor/bin/phpunit src/VisageFour/Bundle/ToolsBundle/Tests/Image/ImageOverlayTest.php --filter generateBatchOfCompositeFiles
     *
     */
    public function generateBatchOfCompositeFiles(): void
    {
        $numberOfCompositesToCreate = 3;

        $template = $this->createImageFileTemplateAndImageOverlayEntites();
        $this->em->flush();

        $imageFile = $template->getRelatedOriginalFile();
        $payload = array (
            'url'   => 'http://www.NewToMelbourne.org/product8?coupon=4422asds'
        );

        $batch = $this->overlayManager->createNewBatch($numberOfCompositesToCreate, $imageFile, $template, $payload);
        $this->em->flush();

        $this->testingHelper->assertNumberOfDBTableRecords($numberOfCompositesToCreate, TrackedFile::class, $this);
        $this->testingHelper->assertNumberOfDBTableRecords(1, Batch::class, $this);

        $expectedFileCount = $numberOfCompositesToCreate +1;
        $this->testingHelper->assertNumberOfDBTableRecords($expectedFileCount, File::class, $this);

        // manual testing
        // prevent cleanup (i.e. deleteFile()) so you can inspect the files
//        copy($composite->getLocalFilePath(), 'var/overlayTest.png');

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
     * ./vendor/bin/phpunit src/VisageFour/Bundle/ToolsBundle/Tests/Image/ImageOverlayTest.php --filter testBatchNoGeneration
     *
     * checks that batch nos are correct (when they're generated i.e. 1,2,3 etc), and that numbering restarts when for a different template (even on the same image).
     */
    public function testBatchNoGeneration(): void
    {
        $template1 = $this->createImageFileTemplateAndImageOverlayEntites();
        $this->em->flush();

        $imageFile = $template1->getRelatedOriginalFile();
        $payload = array (
            'url'   => 'http://www.NewToMelbourne.org/product8?coupon=4422asds'
        );

        $count = 1;
        $A_batch = $this->overlayManager->createNewBatch($count, $imageFile, $template1, $payload);
        $A_batch2 = $this->overlayManager->createNewBatch($count, $imageFile, $template1, $payload);
        $A_batch3 = $this->overlayManager->createNewBatch($count, $imageFile, $template1, $payload);
        $this->assertBatchNoEquals($A_batch3, 3);
        $this->em->flush();

        $template2 = $this->createImageFileTemplateAndImageOverlayEntites();
        $this->em->flush();
        $B_batch = $this->overlayManager->createNewBatch($count, $imageFile, $template2, $payload);
        $this->assertBatchNoEquals($B_batch, 1);
        $B_batch2 = $this->overlayManager->createNewBatch($count, $imageFile, $template2, $payload);
        $B_batch3 = $this->overlayManager->createNewBatch($count, $imageFile, $template2, $payload);
        $B_batch4 = $this->overlayManager->createNewBatch($count, $imageFile, $template2, $payload);
        $this->em->flush();
        $this->assertBatchNoEquals($B_batch4, 4);

//        $this->testingHelper->assertNumberOfDBTableRecords($count, TrackedFile::class, $this);
//        $this->testingHelper->assertNumberOfDBTableRecords(1, Batch::class, $this);
//        $expectedFileCount = $count +1;
//        $this->testingHelper->assertNumberOfDBTableRecords($expectedFileCount, File::class, $this);

    }

    private function assertBatchNoEquals(Batch $batch, $no)
    {
        $batchNo = $batch->getBatchNo();
        $this->assertEquals($batchNo, $no, 'the batch->batchNo is incorrect. It should be: '. $no .', instead it is: '. $batchNo);
    }
}
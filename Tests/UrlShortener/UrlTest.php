<?php
/*
* created on: 26/11/2021 - 17:51
* by: Cameron
*/


namespace App\VisageFour\Bundle\ToolsBundle\Tests\UrlShortener;

use App\Entity\FileManager\File;
use App\Entity\UrlShortener\Hit;
use App\Entity\UrlShortener\Url;
use App\Repository\UrlShortener\UrlRepository;
use App\VisageFour\Bundle\ToolsBundle\Classes\CustomKernelTestCase;
use Doctrine\ORM\EntityManager;
use VisageFour\Bundle\ToolsBundle\Services\FileManager;

/**
 * Class SecurityTest
 * @package App\Tests\Service
 *
 * === Test Case Documentation: ===
 * Run all tests:
 * - ./vendor/bin/phpunit
 * Run all the tests in this file:
 * - ./vendor/bin/phpunit src/VisageFour/Bundle/ToolsBundle/Tests/UrlShortener/UrlTest.php
 *
 * Create new test case [P-CB-087]
 * https://docs.google.com/presentation/d/1-AYb7xtRScoWsB3jxHnThsBJVgwY8DzEKLacGVCB28c/edit#slide=id.p
 *
 * (comment version: 1.02)
 */
class UrlTest extends CustomKernelTestCase
{
    /**
     * @var UrlRepository
     */
    private $urlRepo;

    private function getServices($debuggingOutputOn)
    {
        $container = self::$kernel->getContainer();

        $this->urlRepo = $container->get('test.'. UrlRepository::class);
//        $this->fileManager->getFileRepo()->setOutputValuesOnCreation($debuggingOutputOn);

        $this->getEntityManager();
    }

    /**
     * Setup that is specific to this test case.
     * (this runs once prior to each test case / method())
     */
    protected function customSetUp()
    {
        $this->getServices(true);

        $this->truncateEntities([
            Url::class,
            Hit::class
        ]);

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
     * ./vendor/bin/phpunit src/VisageFour/Bundle/ToolsBundle/Tests/UrlShortener/UrlTest.php --filter createShortenedURL
     *
     * upload a .txt file to AWS S3 and test overwriting it with another file
     */
    public function createShortenedURL(): void
    {
        self::bootKernel();
        $this->customSetUp();

        $url = $this->urlRepo->createNewShortenedUrl('www.NewToMelbourne.org/product1?coupon=11');

        $this->assertNumberOfDBTableRecords(1, Url::class);


        // todo:
        // ShortUrl: URL, name, code, AttributionTag,
        // duplicate file
        // create file DB record
        // create the template
        // generate new flyer (with QR code) - based on inputs (URL) provided

//        $this->ImageOverlayManager
//        $this->

//        $basepath = 'src/VisageFour/Bundle/ToolsBundle/Tests/TestFiles/DeleteFile';
//        $filepath = $this->duplicateLocalFile($basepath, 'testfile.txt');
//
////        $targetFilepath = 'test/testfile.txt';
////        $this->fileManager->deleteRemoteFile($targetFilepath, false);
//
//        $file = $this->fileManager->persistFile($filepath);
//        $remoteFilepath = $file->getRemoteFilePath();
//
//        $this->em->flush();
//        $this->assertNumberOfDBTableRecords(1, File::class);
//
//        // delete the file (from previous test, to prevent duplicate error)
//        $this->fileManager->deleteFile($file);
//        $this->em->flush();
//        $this->assertNumberOfDBTableRecords(0, File::class);
//
//        $original_exists = (is_file($filepath));
//        $this->assertEquals(false, $original_exists, 'the local file was not deleted during the deletion process');
//
//        $remoteFileIsDeleted = ($this->fileManager->doesRemoteFileExist($remoteFilepath));
//        $this->assertEquals(false, $remoteFileIsDeleted, 'the remote file was not deleted during the deletion process');
    }
}
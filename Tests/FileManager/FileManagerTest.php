<?php
/*
* created on: 21/11/2021 - 13:11
* by: Cameron
*/

namespace App\VisageFour\Bundle\ToolsBundle\Tests\FileManager;

use App\Entity\FileManager\File;
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
 * - ./vendor/bin/phpunit src/VisageFour/Bundle/ToolsBundle/Tests/Purchase/CouponTest.php
 *
 * Create new test case [P-CB-087]
 * https://docs.google.com/presentation/d/1-AYb7xtRScoWsB3jxHnThsBJVgwY8DzEKLacGVCB28c/edit#slide=id.p
 *
 * (comment version: 1.02)
 */
class FileManagerTest extends CustomKernelTestCase
{
    /** @var FileManager */
    private $fileManager;

    private function getServices($debuggingOutputOn)
    {
        $container = self::$kernel->getContainer();

        $this->fileManager = $container->get('test.'. FileManager::class);
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
            File::class
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
     * @param $filepath
     * duplicates a local file, because the file that is used in a test is ussually deleted (during cleanup)
     */
    private function duplicateLocalFile($path, $filename)
    {
        $this->fileManager->throwExceptionIfEndsWith($path, '/');

        $originalFilepath = $path.'/'.$filename;
        $newFilepath = $path.'/' .'copy_of_'. $filename;

        // copy the original, as the local file provided will be deleted
        copy( $originalFilepath, $newFilepath );

        return $newFilepath;
    }

    /**
     * @test
     * ./vendor/bin/phpunit src/VisageFour/Bundle/ToolsBundle/Tests/FileManager/FileManagerTest.php --filter uploadFileToRemoteS3AndDelete
     *
     * upload a .txt file to AWS S3 and test overwriting it with another file
     */
    public function uploadFileToRemoteS3AndDelete(): void
    {
        self::bootKernel();
        $this->customSetUp();

        $basepath = 'src/VisageFour/Bundle/ToolsBundle/Tests/TestFiles/DeleteFile';
        $filepath = $this->duplicateLocalFile($basepath, 'testfile.txt');

//        $targetFilepath = 'test/testfile.txt';
//        $this->fileManager->deleteRemoteFile($targetFilepath, false);

        $file = $this->fileManager->persistFile($filepath);
        $remoteFilepath = $file->getRemoteFilePath();

        $this->em->flush();
        $this->assertNumberOfDBTableRecords(1, File::class);

        // delete the file (from previous test, to prevent duplicate error)
        $this->fileManager->deleteFile($file);
        $this->em->flush();
        $this->assertNumberOfDBTableRecords(0, File::class);

        $original_exists = (is_file($filepath));
        $this->assertEquals(false, $original_exists, 'the local file was not deleted during the deletion process');

        $remoteFileIsDeleted = ($this->fileManager->doesRemoteFileExist($remoteFilepath));
        $this->assertEquals(false, $remoteFileIsDeleted, 'the remote file was not deleted during the deletion process');
    }

    /**
     * @test
     * ./vendor/bin/phpunit src/VisageFour/Bundle/ToolsBundle/Tests/FileManager/FileManagerTest.php --filter uploadDuplicateFilenames
     *
     * Test duplicate filename mitigation on both remote storage and on local storage.
     */
    public function uploadDuplicateFilenames(): void
    {
        self::bootKernel();
        $this->customSetUp();

        $basepath = 'src/VisageFour/Bundle/ToolsBundle/Tests/TestFiles/';
        $filepathA = $this->duplicateLocalFile($basepath .'DuplicateA', 'duplicate.txt');
        $filepathB = $this->duplicateLocalFile($basepath .'DuplicateB', 'duplicate.txt');

        $targetSubFolder = 'tests';
        $fileA = $this->fileManager->persistFile($filepathA, $targetSubFolder);
        $fileB = $this->fileManager->persistFile($filepathB, $targetSubFolder);
        $this->em->flush();

        $this->assertNumberOfDBTableRecords(2, File::class);

        $this->fileManager->getLocalFilepath($fileA);
        $this->fileManager->getLocalFilepath($fileB);

        // clean up
//        $this->fileManager->deleteFile($fileA);
//        $this->fileManager->deleteFile($fileB);
    }

    /**
     * @test
     * ./vendor/bin/phpunit src/VisageFour/Bundle/ToolsBundle/Tests/FileManager/FileManagerTest.php --filter testFileCache
     *
     * Test that the FileManager hits the cache (when requesting the file) and downloads the file when it doesn't exist.
     */
    public function testFileCache(): void
    {
        self::bootKernel();
        $this->customSetUp();

        $basepath = 'src/VisageFour/Bundle/ToolsBundle/Tests/TestFiles/DeleteFile';
        $filepath = $this->duplicateLocalFile($basepath, 'testfile.txt');

        $targetSubFolder = 'testFileCache';
        $file = $this->fileManager->persistFile($filepath, $targetSubFolder);
        $this->em->flush();

        $cachedFilepath = $this->fileManager->getLocalFilepath($file);

        $isCached = $this->fileManager->getIsLastCacheHitSuccessful();
        $this->assertEquals(true, $isCached, 'The file should have been in the cache.');

        unlink($cachedFilepath);

        // the file must be downloaded this time.
        $this->fileManager->getLocalFilepath($file);

        $isCached = $this->fileManager->getIsLastCacheHitSuccessful();
        $this->assertEquals(false, $isCached, 'The file should not be in the cache.');

        // the file should once again be in the local cache.
        $this->fileManager->getLocalFilepath($file);

        $isCached = $this->fileManager->getIsLastCacheHitSuccessful();
        $this->assertEquals(true, $isCached, 'The file should have been in the cache.');

//        $isCached = $this->fileManager->getIsLastCacheHitSuccessful();
//        $this->assertEquals(false, $isCached, 'The file should not have been in the cache.');

        // clean up
        $this->fileManager->deleteFile($file);
        $this->em->flush();
        $this->assertNumberOfDBTableRecords(0, File::class);
    }

    /**
     * @test
     * ./vendor/bin/phpunit src/VisageFour/Bundle/ToolsBundle/Tests/FileManager/FileManagerTest.php --filter downloadFileFromS3
     *
     */
    public function downloadFileFromS3(): void
    {
        self::bootKernel();
        $this->customSetUp();

        $remoteFilepath = 'test/testfile-x.txt';

        // todo: delete the file if its already in the local FS

        $file = $this->fileManager->persistFile($filepath, $targetFilepath);

        $this->assertSame(true, $result, '$this->>fileSystem->writeStream() must return true');

    }
}
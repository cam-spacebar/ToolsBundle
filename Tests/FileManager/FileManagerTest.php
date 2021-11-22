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
     * @test
     * ./vendor/bin/phpunit src/VisageFour/Bundle/ToolsBundle/Tests/FileManager/FileManagerTest.php --filter uploadFileToS3
     *
     * upload a .txt file to AWS S3 and create a DB record for a File entity
     */
    public function uploadFileToS3(): void
    {
        self::bootKernel();
        $this->customSetUp();

        $filepath = 'src/VisageFour/Bundle/ToolsBundle/Tests/TestFiles/testfile.txt';
        $targetFilepath = 'test/testfile-x.txt';

        $file = $this->fileManager->persistFile($filepath, $targetFilepath);

        $this->em->flush();
        $this->assertNumberOfDBTableRecords(1, File::class);

        // delete the file (from previous test, to prevent duplicate error)
        $this->fileManager->deleteRemoteFile($targetFilepath, false);
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
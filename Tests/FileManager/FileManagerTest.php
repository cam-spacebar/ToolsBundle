<?php
/*
* created on: 21/11/2021 - 13:11
* by: Cameron
*/

namespace App\VisageFour\Bundle\ToolsBundle\Tests\FileManager;

use App\Entity\FileManager\File;
use Doctrine\ORM\EntityManager;
use VisageFour\Bundle\ToolsBundle\Services\FileManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

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
class FileManagerTest extends KernelTestCase
{
    /** @var EntityManager */
    private $em;

    /** @var FileManager */
    private $fileManager;

    private function getServices($debuggingOutputOn)
    {
        $container = self::$kernel->getContainer();

        $this->fileManager = $container->get('test.'. FileManager::class);
//        $this->fileManager->getFileRepo()->setOutputValuesOnCreation($debuggingOutputOn);

        $this->em = self::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    /**
     * Setup that is specific to this test case.
     * (this runs once prior to each test case / method())
     */
    protected function customSetUp()
    {
        $this->getServices(true);

//        $person1 = new Person();
//
//        $this->ATag1 = $this->attributionTagRepo->createNew('Facebook');
//        $this->ATag2FBP = $this->attributionTagRepo->createNew('Facebook Page', $this->ATag1);
//
//        $this->badgeProd = new Product(
//            'Attendee badge',
//            'le_badge',
//            'A badge that contains 2 flags: one representing the language the attendee speaks and the other representing the language they are learning.',
//            450
//        );
//
//        $this->regProd = new Product(
//            'Attendee registration',
//            'le_registration',
//            'Registration allows the attendee to enter the Language Exchange event.',
//            1050
//        );
//
//        $this->badgeCoupon = $this->couponRepo->createNew($this->ATag2FBP, $person1, 'zbadge', [$this->badgeProd], '$1.02 off replacement badges', 102);
////        die('asdf');
//
//        $this->registrationCoupon100 = new Coupon(
//            'zreg100',
//            [$this->regProd],
//            '92.21% off membership registration',
//            0,
//            98.21
//        );

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

        // delete the file (from previous test, to prevent duplicate error)
        $this->fileManager->deleteRemoteFile($targetFilepath, false);

        $result = $this->fileManager->persistFile($filepath, $targetFilepath);

        $this->em->flush();
        $this->assertNumberofDBTableRecords(1, File::class);

        $this->assertSame(true, $result, '$this->fileSystem->writeStream() must return true');

    }

    work from here:
- implement truncate entities
- document in process for creating tests
- download a file test

    private function assertNumberofDBTableRecords($expectedCount, $entityName)
    {
        $count = (int) $this->em->getRepository($entityName)
            ->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $this->assertSame(
            $expectedCount,
            $count,
            'Failed: test expect '. $expectedCount .' DB records but instead found '. $count .' records (of: '. $entityName .'). Note: remember to use truncateEntities() at the start of each test.'
        );
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

        // delete the file (from previous test, to prevent duplicate error)
        $this->fileManager->deleteFile($remoteFilepath);

        $result = $this->fileManager->persistFile($filepath, $targetFilepath);

        $this->assertSame(true, $result, '$this->>fileSystem->writeStream() must return true');

    }
}
<?php
/*
* created on: 21/11/2021 - 13:11
* by: Cameron
*/

namespace App\VisageFour\Bundle\ToolsBundle\Tests\FileManager;

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
    /** @var FileManager */
    private $fileManager;

    private function getServices($debuggingOutputOn)
    {
        $container = self::$kernel->getContainer();

        $this->fileManager = $container->get('test.'. FileManager::class);
        $this->fileManager->getFileRepo()->setOutputValuesOnCreation($debuggingOutputOn);

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
     * Test calculation of totals (with and without a coupon)
     */
    public function uploadFileToS3(): void
    {
        self::bootKernel();
        $this->customSetUp();

        $filepath = 'src/VisageFour/Bundle/ToolsBundle/Tests/TestFiles/testfile.txt';
        $targetFilepath = 'test/testfile-x.txt';

        $this->fileManager->PersistFile($filepath, $targetFilepath);

//        $person = $this->personRepo->findOneByEmailCanonical('cameronrobertburns@gmail.com');

//        $items1 = [
//            [
//                'product'   => $this->badgeProd,
//                'quantity'  => 2
//            ],
//            [
//                'product'   => $this->regProd,
//                'quantity'  => 1
//            ]
//        ];
//        $checkout1 = $this->checkoutRepo->createCheckoutByItems($items1, $person);
//        $checkout1->setRelatedCoupon($this->badgeCoupon);
////        $checkout1->outputContentsToConsole();
//
//        $this->assertSame(1746, $checkout1->getTotal(), '$checkout1 total is not correct.');
//
//        $items2 = [
//            [
//                'product'   => $this->badgeProd,
//                'quantity'  => 3
//            ],
//            [
//                'product'   => $this->regProd,
//                'quantity'  => 2
//            ]
//        ];
//
//        $checkout2 = $this->checkoutRepo->createCheckoutByItems($items2, $person);
//        $checkout2->setRelatedCoupon($this->registrationCoupon100);
//        $checkout2->outputContentsToConsole();

//        $this->assertSame(1392, $checkout2->getTotal(), '$checkout2 total is not correct.');

//        $checkout2->setRelatedCoupon(null);
//        $this->assertSame(1392, $checkout2->getTotal(), '$checkout2 (without coupon) total is not correct.');




//        print "\n\nTotal (with coupon of: \"". $checkout->getRelatedCoupon()->getAsString() ."%\" applied): ". $checkout->getTotal() .", total (without discount coupon): ". $checkout->getTotalWithoutCoupon();
    }
}
<?php
/*
* created on: 03/11/2021 - 14:15
* by: Cameron
*/

namespace App\VisageFour\Bundle\ToolsBundle\Tests\Purchase;

namespace VisageFour\Bundle\ToolsBundle\Tests\Purchase;

use App\Entity\Purchase\Checkout;
use App\Entity\Purchase\Coupon;
use App\Entity\Purchase\Product;
use App\Repository\Purchase\CheckoutRepository;
use App\Services\FrontendUrl;
use App\Exceptions\ApiErrorCode;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twencha\Bundle\EventRegistrationBundle\Repository\PersonRepository;
use VisageFour\Bundle\ToolsBundle\Classes\CustomApiTestCase;
use VisageFour\Bundle\ToolsBundle\Entity\Purchase\BaseCoupon;
use VisageFour\Bundle\ToolsBundle\Entity\Purchase\BaseProduct;
use VisageFour\Bundle\ToolsBundle\Repository\BasePersonRepository;
use VisageFour\Bundle\ToolsBundle\Repository\Purchase\BaseCheckoutRepository;
use VisageFour\Bundle\ToolsBundle\Services\PasswordManager;

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
class CouponTest extends KernelTestCase
{
    /** @var Coupon */
    private $registrationCoupon100;
    /** @var Coupon */
    private $badgeCoupon50;

    /** @var Product */
    private $regProd;
    /** @var Product */
    private $badgeProd;

    /**
     * Setup that is specific to this test case.
     * (this runs once prior to each test case / method())
     */
    protected function customSetUp()
    {
        $this->badgeProd = new Product(
            'Attendee badge',
            'le_badge',
            'A badge that contains 2 flags: one representing the language the attendee speaks and the other representing the langauge they are learning.',
            450
        );

        $this->regProd = new Product(
            'Attendee registration',
            'le_registration',
            'Registration allows the attendee to enter the Language Exchange event.',
            1050
        );

        $this->badgeCoupon = new Coupon(
            'zbadge',
            [$this->badgeProd],
            '$1.02 off replacement badges',
            102

        );

        $this->registrationCoupon100 = new Coupon(
            'zreg100',
            [$this->regProd],
            '92.21% off membership registration',
            0,
            98.21
        );

//        $this->checkoutRepository =

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
     * ./vendor/bin/phpunit src/VisageFour/Bundle/ToolsBundle/Tests/Purchase/CouponTest.php --filter applyCouponToCheckout
     *
     * Test calculation of totals (with and without a coupon)
     */
    public function applyCouponToCheckout(): void
    {
        self::bootKernel();
        $this->customSetUp();

        $container = self::$kernel->getContainer();

//        convert all entities to \App from \VisageFour - as they need to have a DB table.

        /** @var $checkoutRepo CheckoutRepository */
        $checkoutRepo = $container->get('test.'. CheckoutRepository::class);

        /** @var $personRepo BasePersonRepository */
        $personRepo = $container->get('test.'. PersonRepository::class);
        $person = $personRepo->findOneByEmailCanonical('cameronrobertburns@gmail.com');

        $items1 = [
            [
                'product'   => $this->badgeProd,
                'quantity'  => 2
            ],
            [
                'product'   => $this->regProd,
                'quantity'  => 1
            ]
        ];
        $checkout1 = $checkoutRepo->createCheckoutByItems($items1, $person);
        $checkout1->setRelatedCoupon($this->badgeCoupon);
        $checkout1->outputContentsToConsole();

        $this->assertSame(1746, $checkout1->getTotal(), '$checkout1 total is not correct.');

        $items2 = [
            [
                'product'   => $this->badgeProd,
                'quantity'  => 3
            ],
            [
                'product'   => $this->regProd,
                'quantity'  => 2
            ]
        ];
        $checkout2 = $checkoutRepo->createCheckoutByItems($items2, $person);
        $checkout2->setRelatedCoupon($this->registrationCoupon100);
        $checkout2->outputContentsToConsole();

        $this->assertSame(1392, $checkout2->getTotal(), '$checkout2 total is not correct.');

//        print "\n\nTotal (with coupon of: \"". $checkout->getRelatedCoupon()->getAsString() ."%\" applied): ". $checkout->getTotal() .", total (without discount coupon): ". $checkout->getTotalWithoutCoupon();
    }
}
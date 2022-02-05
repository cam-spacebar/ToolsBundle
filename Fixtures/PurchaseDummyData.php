<?php
/*
* created on: 04/02/2022 - 13:52
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Fixtures;

use App\Entity\Person;
use App\Entity\Purchase\Checkout;
use App\Entity\Purchase\Product;
use App\Entity\Purchase\PurchaseQuantity;
use VisageFour\Bundle\ToolsBundle\Fixtures\PersonDummyData;

class PurchaseDummyData
{
    // returns a checkout with 3 OTA 'Purchase Products' and corresponding 'Purchase Quantity' classes
    public function getOtaCheckoutDummy1()
    {
        $product1 = new Product(
            'Great Ocean Road - 1 day bus trip',
            'OTA:6gik8BU1U3jhss4QXCFXbX:c8ed6751d:1242:first_release',
            'Pickup location: State Library of Victoria, 328 Swanston St, Melbourne (at: 9:00am)',
            4945
        );
        $product1->addLineItem('ticket: Early bird / first release')
            ->addLineItem('Travel Date: 3/February/2022 - Thursday')
            ->addLineItem('Pickup location: State Library of Victoria, 328 Swanston St, Melbourne (at: 9:00am)');

        $product2 = new Product(
            'Great Ocean Road - 1 day bus trip',
            'OTA:6gik8BU1U3jhss4QXCFXbX:c8ed6751d:1242:second_release',
            'Pickup location: State Library of Victoria, 328 Swanston St, Melbourne (at: 9:00am)',
            4945
        );
        $product2->addLineItem('ticket: second release')
            ->addLineItem('Travel Date: 3/February/2022 - Thursday')
            ->addLineItem('Pickup location: State Library of Victoria, 328 Swanston St, Melbourne (at: 9:00am)');

        $quantity1 = new PurchaseQuantity(2, $product1);
        $quantity2 = new PurchaseQuantity(3, $product2);

        $product3 = new Product(
            'Wilsons Promontory - 1 day bus trip',
            'OTA:6gik8BU1U3jhss4QXCFXbX:c8ed6751d:1242:923160',
            'Pickup location: State Library of Victoria, 328 Swanston St, Melbourne (at: 9:00am)',
            4945
        );
        $product3->addLineItem('ticket: First release / early bird')
            ->addLineItem('Travel Date: 1/March/2022 - Friday')
            ->addLineItem('Pickup location: State Library of Victoria, 328 Swanston St, Melbourne (at: 9:00am)');

        $quantity3 = new PurchaseQuantity(1, $product3);

        $personDummyData = new PersonDummyData();
        $person1 = $personDummyData->getPersonCameron();

        $checkout = new Checkout($person1);

        $checkout->addQuantity($quantity1);
        $checkout->addQuantity($quantity2);
        $checkout->addQuantity($quantity3);

        return $checkout;
    }
}
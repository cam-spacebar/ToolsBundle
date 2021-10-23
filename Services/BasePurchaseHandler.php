<?php

namespace VisageFour\Bundle\ToolsBundle\Services;

use App\Repository\Purchase\ProductRepository;
use App\Services\FrontendUrl;
use App\Exceptions\ApiErrorCode;
use App\VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode\InvalidCartTotalException;
use App\VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode\ProductQuantityInvalidException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use VisageFour\Bundle\ToolsBundle\Entity\Purchase\Product;
use VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode\InvalidProductReferenceException;
use VisageFour\Bundle\ToolsBundle\Interfaces\ApiErrorCodeInterface;
use VisageFour\Bundle\ToolsBundle\Traits\LoggerTrait;

/**
 * Handles payment requests/tokens for stripe
 */
class BasePurchaseHandler
{
    /**
     * @var string
     */
    private $stripe_api_key;

    /**
     * @var ProductRepository
     */
    private $prodRepo;

    /**
     * zz @var TokenStorageInterface
     */

    use LoggerTrait;

    /**
     * BasePurchaseHandler constructor.
     * @param string $stripe_key
     * @param ProductRepository $prodRepo
     */
    public function __construct(string $stripe_key, ProductRepository $prodRepo)
    {
        $this->stripe_api_key   = $stripe_key;
        $this->prodRepo         = $prodRepo;
    }

    public function getProductByReference($ref): ?Product
    {
        $curProd = $this->prodRepo->findOneBy([
            'reference' => $ref
        ]);

        if (empty($curProd)) {
            throw new InvalidProductReferenceException($ref);
        }

        return $curProd;
    }

    /**
     * @param $jsonItems
     * @return array
     * @throws InvalidProductReferenceException
     */
    private function parseJsonItems($jsonItems)
    {
        $items = [];
        foreach($jsonItems as $productRef => $curItem) {
            // get product
            $items[$productRef]['product'] = $this->getProductByReference($productRef);
            $curQuan = $curItem['quantity'];
            if ($curQuan <= 0) {
                throw new ProductQuantityInvalidException($productRef, $curQuan);
            }
            $items[$productRef]['quantity'] = $curQuan;
        }
//        dd($items);

        return $items;
    }

    /**
     * @param string $stripeToken
     * @param int $amount
     * @param array $jsonItems
     * @return string
     * @throws InvalidProductReferenceException
     */
    public function processPaymentRequest(string $stripeToken, int $amount, array $jsonItems)
    {
        $this->verifyPurchaseTotal($amount, $jsonItems);
        $items = $this->parseJsonItems($jsonItems);

//        $this->createCheckout($items);

        // 2. get payment working
        // 2.2: populate the payment entities (And persist)
        // 3. send email on success

//        return $data = [
//            'hi' => 'asdf'
//        ];
//        $token = $request->request->get('stripeToken');

//        \Stripe\Stripe::setApiKey($this->stripe_api_key);
//        \Stripe\Charge::create(array(
//            "amount"        => $amount * 100,
//            "currency"      => "aud",
//            "source"        => $stripeToken,
//            "description"   => "First test charge!"
//        ));

        $data = 'working';
        return $data;
    }

    /**
     * @param $total
     * @param $items
     * @throws InvalidProductReferenceException
     *
     * Check that the $total provided actually equals the of quantity * product price (in the cart items.)
     */
    private function verifyPurchaseTotal($providedTotal, $items)
    {
        $calculatedTotal = 0;
        foreach($items as $productRef => $curItem) {
            // get product
            $curProduct = $this->getProductByReference($productRef);
            $curPrice = $curProduct->getPrice();
            $subTotal = $curPrice * $curItem['quantity'];
            $calculatedTotal = $calculatedTotal + $subTotal;
        }

        $this->logger->info('verifying total provided -- $providedTotal: '. $providedTotal .', $calculatedTotal: '. $calculatedTotal );

        if ($calculatedTotal != $providedTotal) {
            throw new InvalidCartTotalException($providedTotal, $calculatedTotal);
        }


        return true;
    }

}
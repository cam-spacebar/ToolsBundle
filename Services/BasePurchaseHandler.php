<?php

namespace VisageFour\Bundle\ToolsBundle\Services;

use App\Repository\Purchase\ProductRepository;
use App\Services\FrontendUrl;
use App\Exceptions\ApiErrorCode;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
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

    public function getProductByReference($ref)
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
     * @param string $stripeToken
     *
     */
    public function processPaymentRequest(string $stripeToken, $amount, $items)
    {
//        dd($items);
        // 0. Creat fixtures for products: badge, registration
        // 1. Check the amounts for the product are correct (or throw error)
        foreach($items as $productRef => $curItem) {
            // get product
            $this->getProductByReference($productRef);
        }
        // 2. get payment working
        // 2.2: populate the payment entities (And persist)
        // 3. send email on success

//        return $data = [
//            'hi' => 'asdf'
//        ];
//        $token = $request->request->get('stripeToken');

        \Stripe\Stripe::setApiKey($this->stripe_api_key);
        \Stripe\Charge::create(array(
            "amount"        => $amount * 100,
            "currency"      => "aud",
            "source"        => $stripeToken,
            "description"   => "First test charge!"
        ));

        $data = 'hi';
        return $data;
    }

}
<?php

namespace VisageFour\Bundle\ToolsBundle\Services;

use App\Services\FrontendUrl;
use App\Exceptions\ApiErrorCode;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use VisageFour\Bundle\ToolsBundle\Interfaces\ApiErrorCodeInterface;

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
     * zz @var TokenStorageInterface
     */
//    private $tokenStorageInterface;

    public function __construct(string $stripe_key)
    {
        $this->stripe_api_key = $stripe_key;
    }

    /**
     * @param string $stripeToken
     *
     */
    public function processPaymentRequest(string $stripeToken, $amount)
    {
        // 0. Creat fixtures for products: badge, registration
        // 1. Check the amounts for the product are correct (or throw error)
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
    }

}
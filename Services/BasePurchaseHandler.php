<?php

namespace VisageFour\Bundle\ToolsBundle\Services;

use App\Entity\Person;
use App\Entity\Purchase\Checkout;
use App\Entity\Purchase\PurchaseQuantity;
use App\Repository\Purchase\CheckoutRepository;
use App\Repository\Purchase\ProductRepository;
use App\Repository\Purchase\PurchaseQuantityRepository;
use App\Services\FrontendUrl;
use App\Exceptions\ApiErrorCode;
use App\VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode\InvalidCartTotalException;
use App\VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode\ProductQuantityInvalidException;
use Doctrine\ORM\EntityManager;
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
     * @var EntityManager
     */
    private $em;
    /**
     * @var CheckoutRepository
     */
    private $checkoutRepository;
    /**
     * @var PurchaseQuantityRepository
     */
    private $quantityRepo;

    /**
     * zz @var TokenStorageInterface
     */

    use LoggerTrait;

    /**
     * BasePurchaseHandler constructor.
     * @param string $stripe_key
     * @param ProductRepository $prodRepo
     * @param EntityManager $em
     */
    public function __construct(string $stripe_key, ProductRepository $prodRepo, EntityManager $em, CheckoutRepository $checkoutRepository, PurchaseQuantityRepository $quantityRepo)
    {
        $this->stripe_api_key       = $stripe_key;
        $this->prodRepo             = $prodRepo;
        $this->em                   = $em;
        $this->checkoutRepository   = $checkoutRepository;
        $this->quantityRepo         = $quantityRepo;
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
     *
     * Get the product entities/objs (based on the product reference provided).
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

        return $items;
    }

    /**
     * @param $jsonItems
     * @return Checkout
     * @throws InvalidProductReferenceException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * Convert a json parsed array of products and quantities into quantity and product objects (and a checkout object) - all linked together.
     */
    private function createFullCheckoutFromJsonItems(array $jsonItems, Person $person)
    {
        $items = $this->parseJsonItems($jsonItems);
        return $this->checkoutRepository->createCheckoutByItems($items, $person);
    }

    /**
     * @param string $stripeToken
     * @param int $amount
     * @param array $jsonItems
     * @return string
     * @throws InvalidProductReferenceException
     */
    public function processPaymentRequest(string $stripeToken, int $amount, array $jsonItems, Person $person)
    {
        $checkout = $this->createFullCheckoutFromJsonItems($jsonItems, $person);

        $this->verifyPurchaseTotal($amount, $checkout);

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
    private function verifyPurchaseTotal($providedTotal, Checkout $checkout)
    {
        $this->logger->info('in: '. __METHOD__ .'(). $checkout: ', [$checkout]);

        $calculatedTotal = $checkout->getTotal();

        $this->logger->info('verifying total provided -- $providedTotal: '. $providedTotal .', $calculatedTotal: '. $calculatedTotal );

        if ($calculatedTotal != $providedTotal) {
            throw new InvalidCartTotalException($providedTotal, $calculatedTotal);
        }


        return true;
    }

}
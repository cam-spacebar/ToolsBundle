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
use App\VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode\PaymentErrorException;
use App\VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode\ProductQuantityInvalidException;
use Doctrine\ORM\EntityManager;
use Stripe\Customer;
use Stripe\Error\InvalidRequest;
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
    public function processPaymentRequest($stripeToken, int $amount, array $jsonItems, Person $person)
    {
        $checkout = $this->createFullCheckoutFromJsonItems($jsonItems, $person);

        $this->verifyPurchaseTotal($amount, $checkout);

        // 2.2: populate the payment entities (And persist)

        $this->attemptStripePayment($stripeToken, $amount, $person);

        // 3. send email on success

        $data = 'Success';
        return $data;
    }

    private function setStripeAPIKey()
    {
        \Stripe\Stripe::setApiKey($this->stripe_api_key);
    }

    private function attemptStripePayment($stripeToken, int $amount, person $person)
    {
        $this->logger->info('attempting stripe payment for amount: '. $amount .' and person: '. $person->getEmail());
        $this->setStripeAPIKey();
        $tokenVal = $stripeToken['id'];
        $customer = $this->findOrCreateStripeCustomerObj($person, $tokenVal);
        // charge object reference: https://stripe.com/docs/api/charges/object
        try {
            $chargeObj = \Stripe\Charge::create(array(
                "amount"        => $amount,
                "currency"      => "aud",
//                "source"        => $tokenVal,
                "description"   => "First test charge!",
                "customer"      => $person->getStripeCustomerId(),
            ));
            $this->logger->info('stripe charge obj: ', [$chargeObj]);
        } catch (InvalidRequest $e) {
            throw new PaymentErrorException($e);
        }

        return true;
    }

    // retrieve (or create) a stripe customer
    private function findOrCreateStripeCustomerObj (person $person, $tokenVal): Customer  {
        $this->logger->info('stripe token: '. $tokenVal);
        if (empty($person->getStripeCustomerId())) {
            $this->logger->info('creating new stripe customer for email address: '. $person->getEmail());
            // create new stripe customer id
            $customer = \Stripe\Customer::create([
                'email' => $person->getEmail(),
                'source' => $tokenVal
            ]);
            $person->setStripeCustomerId($customer->id);
        } else {
            $this->logger->info('Retrieving stripe customer obj for email: '. $person->getEmail());
            $customer = \Stripe\Customer::retrieve($person->getStripeCustomerId());

            $customer->source = $tokenVal;
            $customer->save();
        }

        $this->em->persist($person);

        return $customer;
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

        $checkout->calculateTotal($this->logger);

        $calculatedTotal = $checkout->getTotal();

        $this->logger->info('verifying total provided -- $providedTotal: '. $providedTotal .', $calculatedTotal: '. $calculatedTotal );

        if ($calculatedTotal != $providedTotal) {
            throw new InvalidCartTotalException($providedTotal, $calculatedTotal);
        }


        return true;
    }

}
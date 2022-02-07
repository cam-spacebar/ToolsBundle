<?php

namespace VisageFour\Bundle\ToolsBundle\Services;

use App\Entity\Person;
use App\Entity\Purchase\Checkout;
use App\Entity\Purchase\Product;
use App\Entity\Purchase\PurchaseQuantity;
use App\OtaNine\Services\ProductFactory;
use App\Repository\Purchase\CheckoutRepository;
use App\Repository\Purchase\ProductRepository;
use App\Repository\Purchase\PurchaseQuantityRepository;
use App\Services\FrontendUrl;
use App\Exceptions\ApiErrorCode;
use App\VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode\CannotConnectToStripeException;
use App\VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode\InvalidCartTotalException;
use App\VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode\PaymentErrorException;
use App\VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode\ProductQuantityInvalidException;
use VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode\CombinedException;
use VisageFour\Bundle\ToolsBundle\Services\ApiStatusCode\CombinedExceptionBuilder;
use VisageFour\Bundle\ToolsBundle\Services\ApiStatusCode\LogException;
use Doctrine\ORM\EntityManager;
use Stripe\Customer;
use Stripe\Error\ApiConnection;
use Stripe\Error\InvalidRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode\InvalidProductReferenceException;
use VisageFour\Bundle\ToolsBundle\Interfaces\ApiErrorCodeInterface;
use VisageFour\Bundle\ToolsBundle\Services\Purchase\ProductResolver;
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
     * @var ProductFactory
     */
    private $OtaProductFactory;

    /**
     * @var ProductResolver
     */
    private $productResolver;
    /**
     * @var LogException
     */
    private $logException;
    /**
     * @var CombinedExceptionBuilder
     */
    private $combinedExceptionBuilder;

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
    public function __construct(string $stripe_key, ProductRepository $prodRepo, EntityManager $em, CheckoutRepository $checkoutRepository, PurchaseQuantityRepository $quantityRepo, ProductFactory $OtaProductFactory, ProductResolver $productResolver, LogException $logException, CombinedExceptionBuilder $combinedExceptionBuilder)
    {
        $this->stripe_api_key           = $stripe_key;
        $this->prodRepo                 = $prodRepo;
        $this->em                       = $em;
        $this->checkoutRepository       = $checkoutRepository;
        $this->quantityRepo             = $quantityRepo;
        $this->OtaProductFactory        = $OtaProductFactory;
        $this->productResolver          = $productResolver;
        $this->logException             = $logException;
        $this->combinedExceptionBuilder = $combinedExceptionBuilder;
    }

    // get the product by it $ref (reference) string
    // if it's prefixed with an "OTA_" string, it will need to be pulled in from a different source.
    public function getProductByReference($ref): ?Product
    {
        throw new \Exception('remove this? 1231qsdc marked: 5 feb 2022');
//        try {
//            $otaProd = $this->OtaProductFactory->getOtaProductByCmsEntryId($ref);
//            return $otaProd;
//        } catch (InvalidProductReferenceException $e) {
//            // continue
//        }
//
//        /** @var Product $curProd */
//        $curProd = $this->prodRepo->findOneBy([
//            'reference' => $ref
//        ]);
//
//        if (empty($curProd)) {
//            throw new InvalidProductReferenceException($ref);
//        }

        return $curProd;
    }

    /**
     * @param $jsonItems
     * @return array
     * @throws InvalidProductReferenceException
     *
     * Get the purchase product objs (based on the product reference provided).
     */
    private function parseJsonItems($jsonItems)
    {
//        $this->logger->info
        $items = [];

        foreach($jsonItems as $productRef => $curItem) {
            try {
                // get product
//            $items[$productRef]['product'] = $this->getProductByReference($productRef);
                $items[$productRef]['product'] = $this->productResolver->getProductByReference($productRef);;
                $curQuan = $curItem['quantity'];
                if ($curQuan <= 0) {
                    throw new ProductQuantityInvalidException($productRef, $curQuan);
                }

                $items[$productRef]['quantity'] = $curQuan;
            } catch (ApiErrorCodeInterface $e) {
                // build a lsit of exceptions
                $this->combinedExceptionBuilder->addException($e, $productRef);
            }
        }
        $this->combinedExceptionBuilder->throwIfHasErrors();

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

        $this->attemptStripePayment($stripeToken, $checkout);

        // todo: 3. send email on success


        return true;
    }

    private function setStripeAPIKey()
    {
        \Stripe\Stripe::setApiKey($this->stripe_api_key);
    }

    private function attemptStripePayment($stripeToken, Checkout $checkout)
    {
        $amount = $checkout->getTotal();
        $person = $checkout->getRelatedPerson();
        $this->logger->info('attempting stripe payment for amount: '. $amount .' and person: '. $person->getEmail());
        $this->setStripeAPIKey();
        $tokenVal = $stripeToken['id'];
        $this->em->persist($checkout);

        // charge object reference: https://stripe.com/docs/api/charges/object
        try {
            $customer = $this->findOrCreateStripeCustomerObj($person, $tokenVal);
            $this->createInvoiceAndPopulate($checkout);
            // old stripe charge method:
//            $chargeObj = \Stripe\Charge::create(array(
//                "amount"        => $amount,
//                "currency"      => "aud",
////                "source"        => $tokenVal,
//                "description"   => "First test charge!",
//                "customer"      => $person->getStripeCustomerId(),
//            ));

        } catch (InvalidRequest $e) {
            $checkout->setStatus(Checkout::ERROR_ON_PAYMENT_ATTEMPT);

            $jsonErr = $e->getJsonBody();
            $errorCode = $jsonErr['error']['code'];
            $checkout->setPaymentCode('stripe::'. $errorCode);

            throw new PaymentErrorException($e, $this->logger);
        } catch (ApiConnection $e) {
            $checkout->setStatus(Checkout::ERROR_ON_PAYMENT_ATTEMPT);
            $checkout->setPaymentCode('custom::no_api_connection' );         // use "custom" to indicate that this was not a code from stripe.
            throw new CannotConnectToStripeException($e);
        }

        $checkout->setStatus(\VisageFour\Bundle\ToolsBundle\Entity\Purchase\Checkout::PAID);

        return true;
    }

    // Create a stripe invoice and add the products to it

    private function createInvoiceAndPopulate (Checkout $checkout)
    {
        $person = $checkout->getRelatedPerson();
        /** @var $curQuantity PurchaseQuantity */
        foreach ($checkout->getRelatedQuantities() as $curI => $curQuantity) {
            $curProduct = $curQuantity->getRelatedProduct();
            $this->logger->info('(Stripe invoice) adding product: '. $curProduct->getReference() .', quantity: '. $curQuantity->getQuantity());
            $this->addQuantityToInvoice($curQuantity);
        }
        $invoice = \Stripe\Invoice::create(array(
            "customer" => $person->getStripeCustomerId()
        ));
        $invoice->pay();

    }

    private function addQuantityToInvoice(PurchaseQuantity $quantity)
    {
        $product = $quantity->getRelatedProduct();
        $person = $quantity->getRelatedCheckout()->getRelatedPerson();

        for ($i = 0; $i < $quantity->getQuantity(); $i++) {
            $curAmount = $product->getPrice();
            $this->logger->info('adding to total invoice amount: '. $curAmount);
            \Stripe\InvoiceItem::create(array(
                "amount"        => $curAmount,
                "currency"      => "aud",
                "customer"      => $person->getStripeCustomerId(),
                "description"   => $product->getReference() .': '. $product->getTitle()
            ));
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
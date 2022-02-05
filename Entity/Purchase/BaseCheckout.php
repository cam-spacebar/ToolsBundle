<?php

namespace VisageFour\Bundle\ToolsBundle\Entity\Purchase;

use App\Entity\Person;
use App\Entity\Purchase\Coupon;
use App\Entity\Purchase\PurchaseQuantity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Psr\Log\LoggerInterface;
use VisageFour\Bundle\ToolsBundle\Entity\BaseEntity;
use Doctrine\ORM\Mapping\MappedSuperclass;

/**
 * @MappedSuperClass
 */
class BaseCheckout extends BaseEntity
{

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Person", inversedBy="relatedCheckouts")
     * @ORM\JoinColumn(name="related_person_id", referencedColumnName="id", nullable=false)
     *
     */
    private $relatedPerson;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="integer", nullable=false)
     *
     * The status of the checkout
     */
    protected $status;

    const AWAITING_PAYMENT = 200;
    const PAID = 300;
    const ERROR_ON_PAYMENT_ATTEMPT = 400;

    /**
     * @var int
     *
     * @ORM\Column(name="paymentCode", type="string", nullable=true)
     *
     * The error code that's returned from the payment gateway (i.e. stripe)
     */
    protected $paymentCode;

    /**
     * @var int
     *
     * @ORM\Column(name="total", type="integer", nullable=false)
     *
     * The total price of all items in the checkout - in cents (not dollars) and INCLUDING discount coupons
     */
    protected $total;

    /**
     * @var \DateTime
     * @ORM\Column(name="paymentDateTime", type="datetime")
     */
    protected $paymentDateTime;

    /**
     * @var int
     *
     * @ORM\Column(name="totalWithoutCoupons", type="integer", nullable=false)
     *
     * The total price of all items in the checkout - in cents (not dollars) and WITHOUT discount coupons
     */
    protected $totalWithoutCoupons;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Purchase\PurchaseQuantity", mappedBy="relatedCheckout")
     *
     */
    protected $relatedQuantities;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Purchase\Coupon", inversedBy="relatedCheckouts")
     * @ORM\JoinColumn(name="related_coupon_id", referencedColumnName="id")
     *
     * a discount coupon used on this checkout.
     *
     * @var Coupon
     */
    protected $relatedCoupon;

    /**
     */
    public function __construct(Person $person, string $status = self::AWAITING_PAYMENT)
    {
//        set person
//        check all quantities have the same person (when added).
//        calculate totals when quantity have changes.

        $this->setRelatedPerson($person);
        $this->status = $status;
        $this->relatedQuantities = new ArrayCollection();
        $this->total = 0;
        $this->totalWithoutCoupons = 0;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Person
     */
    public function getRelatedPerson()
    {
        return $this->relatedPerson;
    }

    /**
     * @param Person $relatedPerson
     */
    public function setRelatedPerson(Person $relatedPerson): void
    {
        $this->relatedPerson = $relatedPerson;
    }

    /**
     * @return integer
     */
    public function getTotalWithoutCoupon(): int
    {
        return $this->totalWithoutCoupons;
    }

    /**
     * @return integer
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @return integer
     */
    public function getTotalAsString(): string
    {
        return $this->getAsCurrencyString($this->total);
    }

    // returns the amount in format: "$43.50 AUD"
    private function getAsCurrencyString(int $amountInCents)
    {
        $val = number_format(($this->getTotal() /100), 2, '.', '');
        return '$'. $val .' AUD';
    }

//    /**
//     * @param int $total
//     */
//    public function setTotal(int $total): void
//    {
//        $this->total = $total;
//    }

    /**
     * @return ArrayCollection
     */
    public function getRelatedQuantities(): ArrayCollection
    {
        return $this->relatedQuantities;
    }

    /**
     * @param \App\Entity\Purchase\PurchaseQuantity $purQuan
     * @param bool $addToOppositeSide
     * @return bool
     */
    public function addQuantity(\App\Entity\Purchase\PurchaseQuantity $purQuan, $addToOppositeSide = true): bool
    {
        if ($this->relatedQuantities->contains($purQuan)) {
            return true;
        }

        $this->relatedQuantities->add($purQuan);
        if ($addToOppositeSide) {
            $purQuan->setRelatedCheckout($this);
        }

        // recalculate the total - as it will have changed.

        $this->calculateTotal();
//        print "\n\ncaclulating totla: ". $this->total . " with outcoupon: ". $this->totalWithoutCoupons;

        return true;
    }

    /**
     * @param \App\Entity\Purchase\PurchaseQuantity $purQuan
     * @param bool $removeFromOppositeSide
     * @return bool
     */
    public function removeQuantity(\App\Entity\Purchase\PurchaseQuantity $purQuan, $removeFromOppositeSide = true): bool
    {
        if ($this->relatedQuantities->contains($purQuan)) {
            return true;
        }

        $this->relatedQuantities->remove($purQuan);
        if ($removeFromOppositeSide) {
            $purQuan->setRelatedCheckout(null);
        }

        // recalculate the total - as it will have changed.
        $this->calculateTotal();

        return true;
    }

    /**
     *
     * calculate totals (both with coupon and without a discount coupon)
     */
    public function calculateTotal(?LoggerInterface $logger = null)
    {
        $this->total = 0;
        $this->totalWithoutCoupons = 0;

        /**
         * @var $curQuantity \App\Entity\Purchase\PurchaseQuantity
         */
        foreach($this->relatedQuantities as $key => $curQuantity) {
            $curProduct = $curQuantity->getRelatedProduct();
            if (!empty($logger)) {
                $logger->info('Product: '. $curProduct->getReference() .': '. $curProduct->getPrice() .' x'. $curQuantity->getQuantity());
            }

            $this->total = $this->total + $curQuantity->getTotal($this->getRelatedCoupon());
            $this->totalWithoutCoupons = $this->totalWithoutCoupons + $curQuantity->getTotalWithoutCoupon();
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    /**
     * @return \DateTime
     */
    public function getPaymentDateTime(): \DateTime
    {
        $this->checkAlreadyPaidFor();
        return $this->paymentDateTime;
    }

    public function getPaymentDateTimeAsString($format)
    {
        return $this->paymentDateTime->format($format);
    }

    private function checkAlreadyPaidFor()
    {
        if ($this->status != self::PAID) {
             throw new \Exception('cannot get payment datetime as the checkout has not yet been paid for.');
        }
    }

    /**
     * @param \DateTime $paymentDateTime
     */
    public function setToPaid(): void
    {
        if ($this->status != self::AWAITING_PAYMENT) {
            throw new \Exception('checkout status must be set to AWAITING_PAYMENT prior to being marked as "PAID"');
        }

        $this->status = self::PAID;
        $this->paymentDateTime = new \DateTime('now');
    }

    private function log()
    {

    }

    /**
     * @return string
     */
    public function getPaymentCode(): string
    {
        return $this->paymentCode;
    }

    /**
     * @param string $code
     */
    public function setPaymentCode(string $paymentCode): void
    {
        $this->paymentCode = $paymentCode;
    }

    /**
     * @return Coupon
     */
    public function getRelatedCoupon(): ?Coupon
    {
        return $this->relatedCoupon;
    }

    /**
     * @param Coupon $relatedCoupon
     * @param bool $addToRelation
     */
    public function setRelatedCoupon(?Coupon $relatedCoupon, $addToRelation = true): void
    {
        if ($addToRelation) {
            if (!empty($relatedCoupon)) {
                $relatedCoupon->addRelatedCheckout($this);
            }

            // recalculate total, as the coupon has changed!
            $this->calculateTotal();
        }

        $this->relatedCoupon = $relatedCoupon;

    }

    private function getCouponAsString()
    {
        if (empty($this->relatedCoupon)) {
            return 'no coupon set';
        } else {
            return $this->relatedCoupon->getAsString();
        }

    }

    private function getCouponsAffectedProductsAsString()
    {
        if (empty($this->getRelatedCoupon())) {
            return 'n/a';
        } else {
            return $this->getRelatedCoupon()->getAffectedProductsAsString();
        }
    }

    public function outputContents($lineBreak = "\n")
    {
        $this->outputContentsToConsole($lineBreak);
    }

    /**
     * Display the contents of this object to the console
     * (ussually used for automated testing)
     */
    public function outputContentsToConsole ($lineBreak = "\n") {
        $lb = $lineBreak;
//        print $lb. "=== Checkout Contents === ". $lb;
        print "coupon: ". $this->getCouponAsString() .$lb;
        print "Products affected by coupon: ". $this->getCouponsAffectedProductsAsString() .$lb;
        $count = 1;

        /**
         * @var $curQuantity PurchaseQuantity
         */
        foreach ($this->relatedQuantities as $curI => $curQuantity) {
            print "- Item #". $count .": product reference: ". $curQuantity->getRelatedProduct()->getReference()
                . ' ('. $curQuantity->getRelatedProduct()->getPrice() .' each)'
                .' x'. $curQuantity->getQuantity()
                .' = '.$curQuantity->getTotal($this->relatedCoupon)
                .' (' . $curQuantity->getTotalWithoutCoupon() .' - without coupon)'.
                $lb
            ;
            $count++;
        }

        print $lb;
        $total = $this->getTotal();
        $totalWithoutCoupon = $this->getTotalWithoutCoupon();
        print "Total: ". $total .$lb;
        print "Total (without coupon): ". $totalWithoutCoupon .$lb;
//        dump($totalWithoutCoupon, $total);
        print "Discounted amount (using coupon): ". ($totalWithoutCoupon - $total) .$lb;
    }
}
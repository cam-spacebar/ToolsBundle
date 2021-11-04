<?php
/*
* created on: 01/11/2021 - 15:12
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Entity\Purchase;

use App\Entity\Person;
use Doctrine\Common\Collections\ArrayCollection;
use VisageFour\Bundle\ToolsBundle\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\MappedSuperclass;
use App\Entity\Purchase\Checkout;
use App\Entity\Purchase\Product;

/**
 * @MappedSuperClass
 *
 * Coupons allows users to receive discounts on products.
 * They also allow us to track "sales people" and what sales are attributed to them.
 */
class BaseCoupon extends BaseEntity
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
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=75)
     *
     * Name of the event series.
     */
    protected $code;

    /**
     * @var string
     *
     * @ORM\Column(name="discount_amount", type="integer")
     *
     * Amount of the discount (in cents)
     */
    protected $discountAmount;

    /**
     * @var string
     *
     * @ORM\Column(name="discount_percent", type="integer")
     *
     * Amount of the discount (as a percent)
     */
    protected $discountPercent;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=150, nullable=true)
     *
     * A public description of what the coupon does (e.g. "")
     **/
    protected $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Person", inversedBy="relatedSalesCoupons")
     * @ORM\JoinColumn(name="related_promoter_person_id", referencedColumnName="id")
     *
     * The "Promoter" (i.e. sales person) responsible for promoting this coupon - if one exists.
     *
     * @var Person
     */
    protected $relatedPromoter;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Purchase\Checkout", mappedBy="relatedCoupon")
     *
     * All checkouts where this coupon has been used/applied.
     */
    protected $relatedCheckouts;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Purchase\Product", inversedBy="relatedCoupons")
     * @ORM\JoinTable(name="coupons_to_affected_products")
     *
     * The products that are affected by the coupon (i.e. what products the coupon can be used on)
     *
     */
    protected $relatedAffectedProducts;

    public function __construct($code, array $affectedProducts, string $description = null, int $discountAmountInCents = null, int $discountPercent = null)
    {
        if(empty($discountAmount) && empty($discountPercent)) {
            throw new \Exception('discountAmount and discountPercent cannot both be empty.');
        }

        if ($discountPercent > 100) {
            throw new \Exception ('discount percent of: '. $discountPercent .'% is not permitted. please provide 100 or less');
        }

        if ($discountPercent <= 0) {
            throw new \Exception ('discount percent of: '. $discountPercent .'% is not permitted. please provide a number above 0 or set to null.');
        }

        $this->code = $code;
        $this->description              = $description;
        $this->discountAmount           = $discountAmountInCents;
        $this->discountPercent          = $discountPercent;

        $this->relatedCheckouts         = new ArrayCollection();
        $this->relatedAffectedProducts  = new ArrayCollection();

        foreach ($affectedProducts as $curI => $curProd) {
            $this->addRelatedAffectedProduct($curProd);
        }
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getDiscountAmount(): string
    {
        return $this->discountAmount;
    }

    /**
     * @param string $discountAmount
     */
    public function setDiscountAmount(string $discountAmount): void
    {
        $this->discountAmount = $discountAmount;
    }

    /**
     * @return string
     */
    public function getDiscountPercent(): string
    {
        return $this->discountPercent;
    }

    /**
     * @param string $discountPercent
     */
    public function setDiscountPercent(string $discountPercent): void
    {
        $this->discountPercent = $discountPercent;
    }

    /**
     * @return Person
     */
    public function getRelatedPromoter(): Person
    {
        return $this->relatedPromoter;
    }

    /**
     * @param Person $relatedPromoter
     * @param bool $addToPerson
     */
    public function setRelatedPromoter(Person $relatedPromoter, $addToRelation = true): void
    {
        if ($addToRelation) {
            $relatedPromoter->addSalesCoupons($this);
        }

        $this->relatedPromoter = $relatedPromoter;
    }

    /**
     * @return ArrayCollection
     */
    public function getRelatedCheckouts()
    {
        return $this->relatedCheckouts;
    }

    /**
     * @param Checkout $checkout
     */
    public function addRelatedCheckout(Checkout $checkout, $addToRelation = true)
    {
        if ($this->relatedCheckouts->contains($checkout)) {
            return true;
        }
        $this->relatedCheckouts->add($checkout);
        if ($addToRelation) {
            $checkout->setRelatedCoupon($this);
        }
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return ArrayCollection
     */
    public function getRelatedAffectedProducts()
    {
        return $this->relatedAffectedProducts;
    }

    /**
     * @param Product $product
     * @param bool $addToOppositeSide
     * @return bool
     */
    public function addRelatedAffectedProduct(Product $product, $addToOppositeSide = true):bool
    {
        if ($this->relatedAffectedProducts->contains($product)) {
            return true;
        }

        $this->relatedAffectedProducts->add($product);
        if ($addToOppositeSide) {
            $product->addRelatedCoupon($this, false);
        }

        return true;
    }

    /**
     * Outputs info on the entity (to the console) when it is created in fixtures.
     * (for more info see: VisageFour > BaseFixture Marker: #sn1la)
     */
    public function fixtureDetails ()
    {
        $promoterEmail = (empty($this->relatedPromoter)) ? 'no promoter' : $this->relatedPromoter->getEmail();
        return ([
//            'title'         => $this->title,
            'amount'            => $this->discountAmount,
            'percent'           => $this->discountPercent,
            'description'       => $this->description,
            'promoter'          => $promoterEmail,
            'affectedProducts'  => $this->getAffectProductsAsString()
        ]);
    }

    public function getAffectProductsAsString()
    {
        $return = '';
        /**
         * @var $curProd \App\Entity\Purchase\Product
         */
        foreach ($this->getRelatedAffectedProducts() as $curI => $curProd) {
            $return = $return . ', '. $curProd->getTitle();
        }

        return $return;
    }

    /**
     * @param Product $product
     * @return bool
     *
     * return true if the discount coupon affects this product.
     */
    public function doesCouponApplyToProduct(Product $product)
    {
        return $this->relatedAffectedProducts->contains($product);
    }

    /**
     * @param Product $product
     * @return int
     * @throws \Exception
     *
     * applies the discount coupon (if coupon affects the provided $product) or:
     * returns the normal price if the coupon doesn't affect the product
     */
    public function getDiscountedPrice(Product $product)
    {
        // check if this product is affected by the coupon:
        if ($this->doesCouponApplyToProduct($product)) {
            if (!empty($this->discountAmount)) {
                $newPrice = $product->getPrice() - $this->discountAmount;
            } elseif(!empty($this->discountPercent)) {
                $newPrice = $product->getPrice() * ($this->discountPercent / 100);
            } else {
                throw new \Exception ('neither discountPercent or discountAmount set');
            }
        } else {
            return $product->getPrice();
        }

        // don't allow a negative number to be returned
        if ($newPrice < 0) {
            return 0;
        }
    }
}
<?php
/*
* created on: 01/11/2021 - 15:12
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Entity\Purchase;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Person;
use App\Entity\Purchase\AttributionTag;
use Doctrine\Common\Collections\ArrayCollection;
use VisageFour\Bundle\ToolsBundle\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\MappedSuperclass;
use App\Entity\Purchase\Checkout;
use App\Entity\Purchase\Product;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiProperty;
/**
 * @MappedSuperClass
 *
 * Coupons allows users to receive discounts on products.
 * They also allow us to track "sales people" and what sales are attributed to them.
 * aaa - ApiResource(
 *     collectionOperations={},
 *     itemOperations={
 *         "get"={
 *             "normalizationContext"={"groups"={"api_coupon:read"}}
 *         }
 *     }
 * )
 */
class BaseCoupon extends BaseEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ApiProperty(identifier=false)
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=75)
     * @Groups({"api_coupon:read"})
     * @ApiProperty(identifier=true)
     *
     * Name of the event series.
     */
    protected $code;

    /**
     * @var string
     *
     * @ORM\Column(name="discount_amount", type="integer")
     * @Groups({"api_coupon:read"})
     *
     * Amount of the discount (in cents)
     */
    protected $discountAmount;

    /**
     * @var string
     *
     * @ORM\Column(name="discount_percent", type="integer")
     * @Groups({"api_coupon:read"})
     *
     * Amount of the discount (as a percent)
     */
    protected $discountPercent;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=150, nullable=true)
     * @Groups({"api_coupon:read"})
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
     * @Groups({"api_coupon:read"})
     *
     * The products that are affected by the coupon (i.e. what products the coupon can be used on)
     *
     */
    protected $relatedAffectedProducts;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Purchase\AttributionTag", mappedBy="relatedCoupons")
     */
    protected $relatedAttributionTags;

    public function __construct($code, array $affectedProducts, string $description = null, int $discountAmountInCents = null, int $discountPercent = null)
    {
        if (!empty($discountPercent)) {
            if ($discountPercent > 100) {
                throw new \Exception ('discount percent of: '. $discountPercent .'% is not permitted. please provide 100 or less');
            }

            if ($discountPercent <= 0) {
                throw new \Exception ('discount percent of: '. $discountPercent .'% is not permitted. please provide a number above 0 or set to null.');
            }
        }

        $this->code = $code;
        $this->description              = $description;
        $this->discountAmount           = $discountAmountInCents;
        $this->discountPercent          = $discountPercent;

        $this->relatedCheckouts         = new ArrayCollection();
        $this->relatedAffectedProducts  = new ArrayCollection();
        $this->relatedAttributionTags   = new ArrayCollection();

        foreach ($affectedProducts as $curI => $curProd) {
            $this->addRelatedAffectedProduct($curProd);
        }

        $this->onlyAllowOneDiscountType();
    }

    // check for errors in discountAmount and discountPercent
    protected function onlyAllowOneDiscountType()
    {
        $codeStr = ' Coupon code: "'. $this->code .'"';
        if(empty($this->discountAmount) && empty($this->discountPercent)) {
            throw new \Exception('discountAmount and discountPercent cannot both be empty.'. $codeStr);
        }

        if(!empty($this->discountAmount) && !empty($this->discountPercent)) {
            throw new \Exception('discountAmount and discountPercent cannot both be set.'. $codeStr);
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
    public function getRelatedPromoter(): ?Person
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
            $relatedPromoter->addRelatedSalesCoupon($this, false);
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
     * @return ArrayCollection
     */
    public function getRelatedAttributionTags()
    {
        return $this->relatedAttributionTags;
    }

    /**
     * @param AttributionTag $attributionTag
     * @param bool $updateOppositeRelation
     */
    public function addRelatedAttributionTag(AttributionTag $attributionTag, $updateOppositeRelation = true)
    {
        if ($updateOppositeRelation) {
            $attributionTag->addRelatedCoupon($this, false);
        }
        $this->relatedAttributionTags->add($attributionTag);
    }

    /**
     * @param AttributionTag $attributionTag
     * @param bool $updateOppositeRelation
     */
    public function removeRelatedAttributionTag(AttributionTag $attributionTag, $updateOppositeRelation = true)
    {
        if ($updateOppositeRelation) {
            $attributionTag->removeRelatedCoupon($this, false);
        }
        $this->relatedAttributionTags->removeElement($attributionTag);
    }

    /**
     * Outputs info on the entity (to the console) when it is created in fixtures.
     * (for more info see: VisageFour > BaseFixture Marker: #sn1la)
     */
    public function getOutputContents ()
    {
        $promoterEmail = (empty($this->relatedPromoter)) ? 'no promoter' : $this->relatedPromoter->getEmail();
        return ([
//            'title'         => $this->title,
            'amount'            => $this->discountAmount,
            'percent'           => $this->discountPercent,
            'description'       => $this->description,
            'promoter'          => $promoterEmail,
            'affectedProducts'  => $this->getAffectedProductsAsString()
        ]);
    }

    public function getAffectedProductsAsString()
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

    public function getAsString()
    {
        return 'discount ($): '. $this->discountAmount . ', discount (%): '. $this->discountPercent;
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
     * Applies the discount coupon (if coupon affects the provided $product) or:
     * returns the normal price if the coupon doesn't affect the product
     */
    public function getDiscountedPrice(Product $product)
    {
        $this->onlyAllowOneDiscountType();

        // check if this product is affected by the coupon:
        if ($this->doesCouponApplyToProduct($product)) {
            if (!empty($this->discountAmount)) {
                $newPrice = $product->getPrice() - $this->discountAmount;
            } elseif(!empty($this->discountPercent)) {
//                print "\n\n============ discounted price!!: ". $product->getReference() ."\n\n";
//                print "==== discoutned price variables: price: ". $product->getPrice() .", discount mulitplyer: ". ((100 - $this->discountPercent)/100)."\n";
                $newPrice = $product->getPrice() * ((100 - $this->discountPercent)/100);
            }
        } else {
            return $product->getPrice();
        }

        // don't allow a negative number to be returned
        if ($newPrice < 0) {
            return 0;
        }

        return $newPrice;
    }

    public function __toString()
    {
        return 'id: '. $this->id;
    }
}
<?php

namespace VisageFour\Bundle\ToolsBundle\Entity\Purchase;

use App\Entity\Purchase\Coupon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\Purchase\PurchaseQuantity;
use VisageFour\Bundle\ToolsBundle\Entity\BaseEntity;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiProperty;

/**
 * @MappedSuperClass
 */
class BaseProduct extends BaseEntity
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
     * @ORM\Column(name="title", type="string", length=128, unique=false, nullable=false)
     * @Groups({"api_coupon:read", "api_product:item:get"})
     *
     * Title of the product (or the variant title - if it has a parent)
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=256, unique=false, nullable=false)
     *
     * a short Description of the product
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="integer", nullable=false)
     * @Groups({"api_coupon:read", "api_product:item:get"})
     *
     * price - in cents.
     */
    protected $price;

    /**
     * @var string
     *
     * @ORM\Column(name="reference", type="string", length=50, unique=true, nullable=false)
     * @Groups({"api_coupon:read", "api_product:item:get"})
     * @ApiProperty(identifier=true)
     *
     * a unique reference to the product (used instead of id)
     *
     */
    protected $reference;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Purchase\PurchaseQuantity", mappedBy="relatedProduct")
     *
     * A link to all previous completed checkouts of this product.
     */
    protected $relatedPurchaseQuantities;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Purchase\Coupon", mappedBy="relatedAffectedProducts")
     *
     * Coupons that can be used on this product
     */
    protected $relatedCoupons;

    /**
     * @var
     *
     * @ORM\Column(name="line_items_serialized", type="blob")
     *
     * a serialized array of custom product items like: "pickup location: xyz", "ticket type: first release" etc.
     *
     */
    private $lineItemsSerialized;

    /**
     * @var array
     * the deserialized version of lineItems
     */
    private $lineItemsArray;

    /**
     * Product constructor.
     * @param $title
     * @param $description
     * @param $price
     */
    public function __construct($title, $reference, $description, $price)
    {
        $this->relatedPurchaseQuantities    = new ArrayCollection();
        $this->relatedCoupons               = new ArrayCollection();

        $this->title            = $title;
        $this->reference        = $reference;
        $this->description      = $description;
        $this->price            = $price;

        $this->lineItemsArray   = unserialize($this->lineItemsSerialized);
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
     * @param PurchaseQuantity $purQuan
     * @param bool $addToOppositeSide
     * @return bool
     */
    public function addRelatedPersonnel(PurchaseQuantity $purQuan, $addToOppositeSide = true)
    {
        if ($this->relatedPurchaseQuantities->contains($purQuan)) {
            return true;
        }

        $this->relatedPurchaseQuantities->add($purQuan);
        if ($addToOppositeSide) {
            $purQuan->setRelatedProduct($this);
        }

        return true;
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
     * @return integer
     */
    public function getPrice(): int
    {
        return $this->price;
    }

    /**
     * @return integer
     */
    public function getPriceWithCouponApplied(Coupon $coupon): int
    {
        return $coupon->getDiscountedPrice($this);
    }

    /**
     * @param string $price
     */
    public function setPrice(integer $price): void
    {
        $this->price = $price;
    }

    /**
     * @return string
     */
    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * @param string $reference
     */
    public function setReference(string $reference): void
    {
        $this->reference = $reference;
    }

    /**
     * Outputs info on the entity (to the console) when it is created in fixtures.
     * (for more info see: VisageFour > BaseFixture Marker: #sn1la)
     */
    public function fixtureDetails ()
    {
        return ([
            'title'         => $this->title,
            'reference'     => $this->reference,
            'price'         => $this->price,
            'description'   => $this->description
        ]);
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return ArrayCollection
     */
    public function getRelatedCoupons()
    {
        return $this->relatedCoupons;
    }

    /**
     * @param Coupon $coupon
     * @param bool $addToOppositeSide
     * @return bool
     */
    public function addRelatedCoupon(Coupon $coupon, $addToOppositeSide = true): bool
    {
        if ($this->relatedCoupons->contains($coupon)) {
            return true;
        }

        $this->relatedCoupons->add($coupon);
        if ($addToOppositeSide) {
            $coupon->addRelatedAffectedProduct($this, false);
        }

        return true;
    }

    public function addLineItem(string $item, $updateSerialized = true)
    {
        $this->lineItemsArray[] = $item;

        if ($updateSerialized) {
            // update the serialized array.
            // if adding multiple items that will affect performance (if serialized occurs each time an items is added), you can do it manually.
            $this->updateLineItemsSerialized();

        }

        return $this;
    }

    // note: this must be done before $em->flush() otherwise changes to lineItems will not be persisted.
    public function updateLineItemsSerialized()
    {
        $this->lineItemsSerialized = serialize($this->lineItemsArray);
    }

    /**
     * @param mixed $lineItemsSerialized
     * @return BaseProduct
     */
    public function setLineItemsSerialized($lineItemsSerialized)
    {
        $this->lineItemsSerialized = $lineItemsSerialized;
        $this->lineItemsArray = unserialize($lineItemsSerialized);

        return $this;
    }

    public function getLineItems()
    {
        return $this->lineItemsArray;
    }
}
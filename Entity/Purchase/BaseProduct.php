<?php

namespace VisageFour\Bundle\ToolsBundle\Entity\Purchase;

use App\Entity\Purchase\Coupon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\Purchase\PurchaseQuantity;
use VisageFour\Bundle\ToolsBundle\Entity\BaseEntity;
use Doctrine\ORM\Mapping\MappedSuperclass;

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
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=128, unique=false, nullable=false)
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
     *
     * price - in cents.
     */
    protected $price;

    /**
     * @var string
     *
     * @ORM\Column(name="reference", type="string", length=50, unique=true, nullable=false)
     *
     * a unique reference to the product (used instead of id)
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
     * Product constructor.
     * @param $title
     * @param $description
     * @param $price
     */
    public function __construct($title, $reference, $description, $price)
    {
        $this->relatedPurchaseQuantities    = new ArrayCollection();
        $this->relatedCoupons               = new ArrayCollection();

        $this->title        = $title;
        $this->reference    = $reference;
        $this->description  = $description;
        $this->price        = $price;
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
}

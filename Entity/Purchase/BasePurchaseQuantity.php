<?php

namespace VisageFour\Bundle\ToolsBundle\Entity\Purchase;

use App\Entity\Purchase\Coupon;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Purchase\Product;
use App\Entity\Purchase\Checkout;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\MappedSuperclass;
use VisageFour\Bundle\ToolsBundle\Entity\BaseEntity;

/**
 * @MappedSuperClass
 */
class BasePurchaseQuantity extends BaseEntity
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
     * @ORM\Column(name="quantity", type="integer", nullable=false)
     *
     * The quantity of the item in the checkout
     */
    protected $quantity;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Purchase\Product", inversedBy="relatedPurchaseQuantities")
     * @ORM\JoinColumn(name="related_product_id", referencedColumnName="id", nullable=false)
     *
     * @var $relatedProduct Product
     */
    protected $relatedProduct;

    /**
     * @var $relatedCheckout \App\Entity\Purchase\Checkout
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Purchase\Checkout", inversedBy="relatedQuantities")
     * @ORM\JoinColumn(name="related_checkout_id", referencedColumnName="id", nullable=false)
     *
     * If it has a parent, this product is a variant
     */
    protected $relatedCheckout;

    /**
     * PurchaseQuantity constructor.
     * @param integer $quantity
     * @param Product $product
     */
    public function __construct(int $quantity, Product $product)
    {
        $this->setQuantity($quantity);
        $this->setRelatedProduct($product);
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
     * @return string
     */
    public function getQuantity(): string
    {
        return $this->quantity;
    }

    /**
     * @param string $quantity
     */
    public function setQuantity(string $quantity): void
    {
        $this->quantity = $quantity;

        if (!empty($this->relatedCheckout)) {
            // when the quantity changes, the checkout total should be updated (if one exists)
            $this->relatedCheckout->calculateTotal();
        }
    }

    /**
     * @return Product
     */
    public function getRelatedProduct()
    {
        return $this->relatedProduct;
    }

    /**
     * @param Product $relatedProduct
     */
    public function setRelatedProduct(Product $relatedProduct): void
    {
        $this->relatedProduct = $relatedProduct;
    }

    /**
     * @return Checkout
     */
    public function getRelatedCheckout(): Checkout
    {
        return $this->relatedCheckout;
    }

    /**
     * @param Checkout $relatedCheckout
     */
    public function setRelatedCheckout(Checkout $relatedCheckout): void
    {
        $this->relatedCheckout = $relatedCheckout;
    }

    /**
     * Return the total of the products price * quantity amount (apply the discount coupon - if one exists)
     */
    public function getTotal(?Coupon $coupon = null)
    {
        if (!empty($coupon)) {
//            print '=--======'."\n";
            return $coupon->getDiscountedPrice($this->relatedProduct) * $this->getQuantity();
        } else {
            return $this->getTotalWithoutCoupon();
        }

    }

    public function getTotalAsString(?Coupon $coupon = null)
    {
        return $this->getAsCurrencyString($this->getTotal($coupon));
    }

    // returns the amount in format: "$43.50 AUD"
    private function getAsCurrencyString(int $amountInCents)
    {
        $val = number_format(($this->getTotal() /100), 2, '.', '');
        return '$'. $val .' AUD';
    }

    /**
     * Return the total of the products price * quantity amount
     */
    public function getTotalWithoutCoupon()
    {
//        print $this->relatedProduct->getPrice() .' * '. $this->getQuantity() .'. ';
        return $this->relatedProduct->getPrice() * $this->getQuantity();
    }
}
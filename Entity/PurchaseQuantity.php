<?php

namespace VisageFour\Bundle\ToolsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="purchase_quantity")
 * @ORM\Entity(repositoryClass="Twencha\Bundle\EventRegistrationBundle\Repository\PurchaseQuantityRepository")
 */
class PurchaseQuantity extends BaseEntity
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
     * @ORM\OneToMany(targetEntity="Product", mappedBy="relatedPurchaseQuantities")
     *
     */
    private $relatedProduct;

    /**
     * zz @ORM\ManyToOne(targetEntity="Twencha\Bundle\EventRegistrationBundle\Entity\Round", inversedBy="variantProducts")
     * zz @ORM\JoinColumn(name="related_product_parent_id", referencedColumnName="id", nullable=true)
     *
     * if it has a parent, this product is a variant
     */
//    private $product;

    /**
     * PurchaseQuantity constructor.
     * @param $quantity
     * @param Product $product
     */
    public function __construct($quantity, Product $product)
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
}

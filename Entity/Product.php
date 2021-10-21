<?php

namespace VisageFour\Bundle\ToolsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="purchase_product")
 * @ORM\Entity(repositoryClass="Twencha\Bundle\EventRegistrationBundle\Repository\PurchaseQuantityRepository")
 */
class Product extends BaseEntity
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
     * @ORM\OneToMany(targetEntity="PurchaseQuantity", mappedBy="relatedProduct")
     *
     * A link to all previous completed checkouts of this product
     */
    private $relatedPurchaseQuantities;

    /**
     * zzz  @ORM\ManyToOne(targetEntity="Twencha\Bundle\EventRegistrationBundle\Entity\Round", inversedBy="variantProducts")
     * zzz @ORM\JoinColumn(name="related_product_parent_id", referencedColumnName="id", nullable=true)
     *
     * todo: complete this
     * if it has a parent, this product is a variant
     */
//    private $parentProduct;

    public function __construct()
    {
        $this->relatedPurchaseQuantities      = new ArrayCollection();
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
}

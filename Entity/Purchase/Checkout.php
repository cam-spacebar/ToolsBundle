<?php

namespace VisageFour\Bundle\ToolsBundle\Entity\Purchase;

use App\Entity\Person;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use VisageFour\Bundle\ToolsBundle\Entity\BaseEntity;
use Doctrine\ORM\Mapping\MappedSuperclass;

/**
 * @MappedSuperClass
 */
class Checkout extends BaseEntity
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
     * the status of the checkout
     */
    protected $status;

    const AWAITING_PAYMENT = 200;
    const PAID = 300;
    const ERROR_ON_PAYMENT_ATTEMPT = 400;

    /**
     * @var int
     *
     * @ORM\Column(name="total", type="integer", nullable=false)
     *
     * The total price of all items in the checkout - in cents (not dollars)
     */
    protected $total;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Purchase\PurchaseQuantity", mappedBy="relatedCheckout")
     *
     */
    protected $relatedQuantities;

    /**
     */
    public function __construct()
    {

        $this->relatedQuantities = new ArrayCollection();
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
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @param string $total
     */
    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

    /**
     * @return ArrayCollection
     */
    public function getRelatedQuantities(): ArrayCollection
    {
        return $this->relatedQuantities;
    }

    /**
     * @param \App\Entity\PurchaseQuantity $purQuan
     * @param bool $addToOppositeSide
     * @return bool
     */
    public function addQuantity(\App\Entity\PurchaseQuantity $purQuan, $addToOppositeSide = true): bool
    {
        if ($this->relatedQuantities->contains($purQuan)) {
            return true;
        }

        $this->relatedQuantities->add($purQuan);
        if ($addToOppositeSide) {
            $purQuan->setRelatedCheckout($this);
        }

        return true;
    }
}

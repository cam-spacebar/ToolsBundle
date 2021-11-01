<?php
/*
* created on: 01/11/2021 - 15:12
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Entity\Purchase;

use App\Entity\Person;
use VisageFour\Bundle\ToolsBundle\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\MappedSuperclass;

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
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=75)
     *
     * Name of the event series.
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="discount_amount", type="integer")
     *
     * Amount of the discount (in cents)
     */
    private $discountAmount;

    /**
     * @var string
     *
     * @ORM\Column(name="discount_percent", type="integer")
     *
     * Amount of the discount (as a percent)
     */
    private $discountPercent;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Person", inversedBy="salesCoupons")
     * @ORM\JoinColumn(name="related_promoter_person_id", referencedColumnName="id")
     *
     * the "promoter" (i.e. sales person) responsible for promoting this coupon - if one exists.
     *
     * @var Person
     */
    private $relatedSalesPerson;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Person", inversedBy="salesCoupons")
     * @ORM\JoinColumn(name="related_promoter_person_id", referencedColumnName="id")
     *
     * The "Promoter" (i.e. sales person) responsible for promoting this coupon - if one exists.
     *
     * @var Person
     */
    private $relatedPromoter;

    // todo:
    // checkout one to many
    // internal description

    public function __construct($code)
    {
        $this->code = $code;
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
     */
    public function setRelatedPromoter(Person $relatedPromoter): void
    {
        $this->relatedPromoter = $relatedPromoter;
    }

    /**
     * @return Person
     */
    public function getRelatedSalesPerson(): Person
    {
        return $this->relatedSalesPerson;
    }

    /**
     * @param Person $relatedSalesPerson
     * @param bool $addToPerson
     */
    public function setRelatedSalesPerson(Person $relatedSalesPerson, $addToPerson = true): void
    {
        if ($addToPerson) {
            $relatedSalesPerson->addSalesCoupons($this);
        }

        $this->relatedSalesPerson = $relatedSalesPerson;
    }
}
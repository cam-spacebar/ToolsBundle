<?php
/*
* created on: 05/11/2021 - 11:45
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Entity\Purchase;

use App\Entity\Purchase\Coupon;
use Doctrine\Common\Collections\ArrayCollection;
use VisageFour\Bundle\ToolsBundle\Entity\BaseTag;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\MappedSuperclass;

/**
 * @MappedSuperClass
 * Class AttributionTag
 * @package App\VisageFour\Bundle\ToolsBundle\Entity\Purchase
 *
 * Designed to attribute sales to particular channels (ussually via discount coupons)
 */
class BaseAttributionTag extends BaseTag
{
    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Purchase\Coupon", inversedBy="relatedAttributionTags")
     * @ORM\JoinTable(name="attribution_tag_to_coupon")
     *
     * Coupons that are using this tag
     */
    protected $relatedCoupons;

    public function __construct () {
        $this->relatedCoupons     = new ArrayCollection();
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
     * @param bool $updateOppositeRelation
     */
    public function addRelatedCoupon(Coupon $coupon, $updateOppositeRelation = true)
    {
        if ($updateOppositeRelation) {
            $coupon->addRelatedAttributionTag($this);
        }
        $this->relatedCoupons->add($coupon);
    }

    /**
     * @param Coupon $relatedCoupon
     * @param bool $updateManyToManyReference
     */
    public function removeRelatedCoupon(Coupon $coupon, $updateOppositeRelation = true)
    {
        if ($updateOppositeRelation) {
            $coupon->removeRelatedAttributionTag($this, false);
        }
        $this->relatedCoupons->removeElement($coupon);
    }
}
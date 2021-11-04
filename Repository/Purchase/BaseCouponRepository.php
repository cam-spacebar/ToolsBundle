<?php
/*
* created on: 01/11/2021 - 20:25
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Repository\Purchase;

use Doctrine\Persistence\ManagerRegistry;
use VisageFour\Bundle\ToolsBundle\Entity\Purchase\BaseCoupon;
use VisageFour\Bundle\ToolsBundle\Repository\NoAutowire\BaseRepository;

class BaseCouponRepository extends BaseRepository
{
    public function __construct (ManagerRegistry $registry, $class = BaseCoupon::class) {
        parent::__construct($registry, $class);
    }

}
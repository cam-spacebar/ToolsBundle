<?php
/*
* created on: 01/11/2021 - 20:25
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Repository\Purchase;

use Doctrine\Persistence\ManagerRegistry;
use VisageFour\Bundle\ToolsBundle\Repository\BaseRepository;

class BaseCouponRepository extends BaseRepository
{
    public function __construct (ManagerRegistry $registry, $class) {
        parent::__construct($registry, $class);
    }

}
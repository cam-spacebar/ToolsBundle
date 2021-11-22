<?php

namespace VisageFour\Bundle\ToolsBundle\Repository\Purchase;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use VisageFour\Bundle\ToolsBundle\Entity\Purchase\BaseProduct;
use VisageFour\Bundle\ToolsBundle\Repository\NoAutowire\BaseRepository;

/**

 */
class BaseProductRepository extends BaseRepository
{
    public function __construct (ManagerRegistry $registry, $class = BaseProduct::class) {
        parent::__construct($registry, $class);
    }
//    public function countSpooled () {
//        $qb = $this->createQueryBuilder('er')
//            ->select('COUNT(er)')
//            ->where('er.sendStatus = :status')
//            ->setParameter('status',     EmailRegister::SPOOLED)
//        ;
//
//        return  $qb->getQuery()
//            ->getSingleScalarResult()
//            ;
//    }
}
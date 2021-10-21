<?php

namespace VisageFour\Bundle\ToolsBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use VisageFour\Bundle\ToolsBundle\Entity\PurchaseQuantity;

/**

 */
class PurchaseQuantityRepository extends ServiceEntityRepository
{
    public function __construct (ManagerRegistry $registry) {
        parent::__construct($registry, PurchaseQuantity::class);
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

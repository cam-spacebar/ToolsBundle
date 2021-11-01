<?php

namespace VisageFour\Bundle\ToolsBundle\Repository\Purchase;

use App\Entity\Purchase\Product;
use App\Entity\Purchase\PurchaseQuantity;
use VisageFour\Bundle\ToolsBundle\Repository\BaseRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use VisageFour\Bundle\ToolsBundle\Traits\LoggerTrait;

/**

 */
class PurchaseQuantityRepository extends BaseRepository
{

    public function __construct (ManagerRegistry $registry, $class) {
        parent::__construct($registry, $class);
    }

    public function createNew(int $quantity, Product $product)
    {
        $curQuantity = new PurchaseQuantity($quantity, $product);
        $this->persistAndLogEntityCreation($curQuantity);

        return $curQuantity;
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
<?php

namespace VisageFour\Bundle\ToolsBundle\Repository\Purchase;

use App\Entity\Person;
use App\VisageFour\Bundle\ToolsBundle\Repository\BaseRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Purchase\Checkout;

/**

 */
class CheckoutRepository extends BaseRepository
{
    public function __construct (ManagerRegistry $registry, $class) {
        parent::__construct($registry, $class);
    }

    public function createNew(Person $person)
    {
        $checkout = new Checkout($person);
        $this->logEntityCreationAndPersist($checkout);

        return $checkout;
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
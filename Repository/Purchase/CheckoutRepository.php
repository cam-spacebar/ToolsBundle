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
    /**
     * @var \App\Repository\Purchase\PurchaseQuantityRepository
     */
    private $quantityRepo;

    public function __construct (ManagerRegistry $registry, $class, \App\Repository\Purchase\PurchaseQuantityRepository $quanRepo) {
        parent::__construct($registry, $class);
        $this->quantityRepo = $quanRepo;
    }

    public function createNew(Person $person)
    {
        $checkout = new Checkout($person);
        $this->logEntityCreationAndPersist($checkout);

        return $checkout;
    }

    /**
     * * $items array accepts elements with properties of:
     * - ['product'] => Product obj
     * - ['quantity'] => int
     */
    public function createCheckoutByItems(array $items, Person $person): Checkout
    {
        $this->logger->info('in: '. __METHOD__ .'(). items: ', $items );
        $checkout = $this->createNew($person);

        foreach($items as $productRef => $curItem) {
            $curProduct = $curItem['product'];
            $curQuantity = $this->quantityRepo->createNew($curItem['quantity'], $curProduct);

            $checkout->addQuantity($curQuantity);

//            $this->em->persist($curQuantity);
//            $this->em->persist($checkout);
        }

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
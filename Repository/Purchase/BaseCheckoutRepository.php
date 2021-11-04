<?php

namespace VisageFour\Bundle\ToolsBundle\Repository\Purchase;

use App\Entity\Person;
use VisageFour\Bundle\ToolsBundle\Entity\Purchase\BaseCheckout;
use VisageFour\Bundle\ToolsBundle\Repository\NoAutowire\BaseRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Purchase\Checkout;
use App\Repository\Purchase\PurchaseQuantityRepository;

/**

 */
class BaseCheckoutRepository extends BaseRepository
{
    /**
     * @var PurchaseQuantityRepository
     */
    private $quantityRepo;

    public function __construct (ManagerRegistry $registry, PurchaseQuantityRepository $quanRepo, $class = BaseCheckout::class) {
        parent::__construct($registry, $class);
        $this->quantityRepo = $quanRepo;
    }

    public function createNew(Person $person)
    {
        $checkout = new Checkout($person);
        $this->persistAndLogEntityCreation($checkout);

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
//            dump($curItem);
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
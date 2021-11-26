<?php
/*
* created on: 26/11/2021 - 15:44
* by: Cameron
*/

namespace App\VisageFour\Bundle\ToolsBundle\Repository\UrlShortener;

use App\Entity\UrlShortener\Hit;
use VisageFour\Bundle\ToolsBundle\Repository\NoAutowire\BaseRepository;


/**
 * @method Hit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Hit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Hit[]    findAll()
 * @method Hit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BaseHitRepository extends BaseRepository
{

    public function createNewxxxx (string $filename)
    {
//        $new = new ???();

//        $this->persistAndLogEntityCreation($new);

//        return $new;
    }

    // /**
    //  * @return Hit[] Returns an array of Hit objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('h.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Hit
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

}
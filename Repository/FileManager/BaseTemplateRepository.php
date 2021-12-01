<?php
/*
* created on: 25/11/2021 - 17:07
* by: Cameron
*/

namespace App\VisageFour\Bundle\ToolsBundle\Repository\FileManager;

use App\Entity\FileManager\Template;
use VisageFour\Bundle\ToolsBundle\Repository\NoAutowire\BaseRepository;

/**
 * @method Template|null find($id, $lockMode = null, $lockVersion = null)
 * @method Template|null findOneBy(array $criteria, array $orderBy = null)
 * @method Template[]    findAll()
 * @method Template[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BaseTemplateRepository extends BaseRepository
{

    public function createNewTemplate (string $filename)
    {
//        $new = new ???();

//        $this->persistAndLogEntityCreation($new);

//        return $new;
    }

    // /**
    //  * @return Template[] Returns an array of Template objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Template
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
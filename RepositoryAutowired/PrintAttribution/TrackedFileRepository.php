<?php
/*
* created on: 01/12/2021 - 13:25
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\RepositoryAutowired\PrintAttribution;

use Doctrine\Persistence\ManagerRegistry;
use VisageFour\Bundle\ToolsBundle\Entity\PrintAttribution\Batch;
use VisageFour\Bundle\ToolsBundle\Entity\PrintAttribution\TrackedFile;
use VisageFour\Bundle\ToolsBundle\Repository\NoAutowire\BaseRepository;

/**
 * @method TrackedFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method TrackedFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method TrackedFile[]    findAll()
 * @method TrackedFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrackedFileRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TrackedFile::class);
    }

    public function createNewTrackedFile(Batch $batch, int $order): TrackedFile
    {
        $new = new TrackedFile($batch, $order);

        $this->persistAndLogEntityCreation($new);

        return $new;
    }

    // /**
    //  * @return TrackedFile[] Returns an array of TrackedFile objects
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
    public function findOneBySomeField($value): ?TrackedFile
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

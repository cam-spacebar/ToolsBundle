<?php

namespace VisageFour\Bundle\ToolsBundle\RepositoryAutowired\PrintAttribution;

use App\Entity\FileManager\Template;
use VisageFour\Bundle\ToolsBundle\Entity\PrintAttribution\Batch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use VisageFour\Bundle\ToolsBundle\Repository\NoAutowire\BaseRepository;

/**
 * @method Batch|null find($id, $lockMode = null, $lockVersion = null)
 * @method Batch|null findOneBy(array $criteria, array $orderBy = null)
 * @method Batch[]    findAll()
 * @method Batch[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BatchRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Batch::class);
    }

    public function createNewBatch(Template $template, array $payload)
    {
        // get next "batchNo" in the series
        $batchNo = $this->getNextBatchNo($template);

        $new = new Batch($template, $payload, $batchNo);

        $this->persistAndLogEntityCreation($new, true);
        $new->setPayload($payload);

        return $new;
    }

    public function getNextBatchNo(Template $template)
    {
        $highest = $this->em->createQueryBuilder()
            ->select('MAX(e.id)')
            ->from('YourBundle:Entity', 'e')
            ->getQuery()
            ->getSingleScalarResult();

        dump($highest);
        return $highest;

    }

    // /**
    //  * @return Batch[] Returns an array of Batch objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Batch
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
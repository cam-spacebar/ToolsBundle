<?php
/*
* created on: 22/11/2021 - 20:57
* by: Cameron
*/

namespace App\VisageFour\Bundle\ToolsBundle\Classes;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CustomKernelTestCase extends KernelTestCase
{
    /** @var EntityManager */
    protected $em;

    public function getEntityManager (): EntityManager
    {
        $this->em = self::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        return $this->em;
    }

    protected function assertNumberOfDBTableRecords($expectedCount, $entityName)
    {
        $count = (int) $this->em->getRepository($entityName)
            ->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $this->assertSame(
            $expectedCount,
            $count,
            'Failed: test expect '. $expectedCount .' DB records but instead found '. $count .' records (of entity class: '. $entityName .'). (note: remember to use truncateEntities() at the start of each test.)'
        );
    }

    /**
     * @param array $entities
     * Remove records from entity tables
     */
    protected function truncateEntities(array $entities)
    {
        $connection = $this->getEntityManager()->getConnection();
        $databasePlatform = $connection->getDatabasePlatform();
        if ($databasePlatform->supportsForeignKeyConstraints()) {
            $connection->query('SET FOREIGN_KEY_CHECKS=0');
        }
        foreach ($entities as $entity) {
            $query = $databasePlatform->getTruncateTableSQL(
                $this->getEntityManager()->getClassMetadata($entity)->getTableName()
            );
            $connection->executeUpdate($query);
        }
        if ($databasePlatform->supportsForeignKeyConstraints()) {
            $connection->query('SET FOREIGN_KEY_CHECKS=1');
        }
    }
}
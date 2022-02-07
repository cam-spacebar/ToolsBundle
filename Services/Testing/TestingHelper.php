<?php
/*
* created on: 27/11/2021 - 12:28
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Services\Testing;

use Doctrine\ORM\EntityManager;

/**
 * Class TestingHelper
 * @package VisageFour\Bundle\ToolsBundle\Services\Testing
 *
 * TestingHelper centralizes common testing methods for both ApiTestCase and KernelTestCase (as inheritence of both of these test classes was not a suitable option).
 */
class TestingHelper
{
    /** @var EntityManager */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em  = $em;
    }

    // remember to use this with truncateEntities() in the tests: customSetUp()
    public function assertNumberOfDBTableRecords(int $expectedCount, string $entityName, object $testCase)
    {
        $count = (int) $this->em->getRepository($entityName)
            ->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $testCase->assertSame(
            $expectedCount,
            $count,
            'Failed: test expect '. $expectedCount .' DB records but instead found '. $count .' records (of entity class: '. $entityName .'). (note: remember to use truncateEntities() at the start of each test - in customSetUp().)'
        );

        return true;
    }

    /**
     * @param array $entities
     * Remove records from entity tables
     */
    public function truncateEntities(array $entities)
    {
        $connection = $this->em->getConnection();
        $databasePlatform = $connection->getDatabasePlatform();
        if ($databasePlatform->supportsForeignKeyConstraints()) {
            $connection->query('SET FOREIGN_KEY_CHECKS=0');
        }
        foreach ($entities as $entity) {
            $query = $databasePlatform->getTruncateTableSQL(
                $this->em->getClassMetadata($entity)->getTableName()
            );
            $connection->executeUpdate($query);
        }
        if ($databasePlatform->supportsForeignKeyConstraints()) {
            $connection->query('SET FOREIGN_KEY_CHECKS=1');
        }
    }
}
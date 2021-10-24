<?php
/*
* created on: 24/10/2021 - 21:51
* by: cameron
*/

namespace App\VisageFour\Bundle\ToolsBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use VisageFour\Bundle\ToolsBundle\Traits\LoggerTrait;
use VisageFour\Bundle\ToolsBundle\Traits\EntityManagerTrait;

class BaseRepository extends ServiceEntityRepository
{
    use LoggerTrait;
    use EntityManagerTrait;

    protected function logEntityCreationAndPersist($newObj, $persist = true)
    {
        $className = (new \ReflectionClass($newObj))->getShortName();
        $this->logger->info(
            'Creating new entity: '. $className,
            [$newObj]
        );

        if ($persist) {
            $this->persist($newObj);
        }
    }
}
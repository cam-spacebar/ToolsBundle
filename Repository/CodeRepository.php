<?php

namespace VisageFour\Bundle\ToolsBundle\Repository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use VisageFour\Bundle\ToolsBundle\Entity\Code;

/**
 * CodeRepository
 */
class CodeRepository extends ServiceEntityRepository
{
    public function __construct (ManagerRegistry $registry, $class) {
        parent::__construct($registry, $class);
    }

    public function getByCode($code) : Code
    {
        return $this->findOneBy([
            'code' => $code
        ]);
    }
}

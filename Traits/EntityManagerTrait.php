<?php
/*
* created on: 24/10/2021 - 21:56
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Traits;

use Doctrine\ORM\EntityManager;

trait EntityManagerTrait
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @required
     * note: required is what tells symfony to call this and inject $logger
     */
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }

    private function persist($obj)
    {
        $this->em->persist($obj);
    }
}
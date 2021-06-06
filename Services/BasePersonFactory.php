<?php

namespace VisageFour\Bundle\ToolsBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;

// todo: copy some code from the twenchaLE PersonFactory to create some generic methods.
class BasePersonFactory
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Faker\Generator
     */
    protected $faker;

    public function __construct(
        EntityManagerInterface $em
    ) {
        $this->faker = Factory::create();
        $this->em                   = $em;
    }
}
<?php

namespace VisageFour\Bundle\ToolsBundle\Services;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use NewInTown\NewInTownBundle\Entity\JobApplication;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twencha\Bundle\EventRegistrationBundle\Entity\Event;
use Twencha\Bundle\EventRegistrationBundle\Entity\EventSeries;
use Twencha\Bundle\EventRegistrationBundle\Entity\Slug;
use VisageFour\Bundle\ToolsBundle\Services\BaseEntityManager;

class SlugManager extends BaseEntityManager
{
    /**
     * EventSeriesManager constructor.
     * @param EntityManager $em
     * @param $class
     * @param EventDispatcherInterface $dispatcher
     * @param Logger $logger
     */
    public function __construct(EntityManager $em, $class, EventDispatcherInterface $dispatcher, Logger $logger) {
        parent::__construct($em, $class, $dispatcher, $logger);
    }

    public function createNew () {
        $result = new Slug();

        return $result;
    }

    // todo: write get slug by code
    
}
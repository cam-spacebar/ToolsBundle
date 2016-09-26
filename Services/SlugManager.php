<?php

namespace VisageFour\Bundle\ToolsBundle\Services;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use NewInTown\NewInTownBundle\Entity\JobApplication;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twencha\Bundle\EventRegistrationBundle\Entity\Event;
use Twencha\Bundle\EventRegistrationBundle\Entity\EventSeries;
use Twencha\Bundle\EventRegistrationBundle\Entity\Slug;
use Twencha\Bundle\EventRegistrationBundle\Entity\Source;
use VisageFour\Bundle\ToolsBundle\Entity\Code;
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
    
}
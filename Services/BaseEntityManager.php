<?php

namespace VisageFour\Bundle\ToolsBundle\Services;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class BaseEntityManager
{
    /*
                === USAGE BELOW! ===
    /**
     * RegisteredCodesManager constructor.
     * @param EntityManager $em
     * @param $class
     * @param EventDispatcherInterface $dispatcher
     * @param LoggerInterface $logger
    ZZZ
    public function __construct(EntityManager $em, $class, EventDispatcherInterface $dispatcher, LoggerInterface $logger) {
        parent::__construct($em, $class, $dispatcher, $logger);
        // custom config
        // ...
    }

    public function customCreateNew ($persist = true) {
        // instantiate
        $event = parent::createNew(false);

        // configure
        // ...

        // persist
        if ($persist) {
            $this->em->persist($event);
            $persistStatus = 'true';
        } else {
            $persistStatus = 'false';
        }

        return $event;
    }

    ===== SERVICE DEFINITION in YML =====
        tools_bundle.event_series_manager:
        class: Twencha\Bundle\EventRegistrationBundle\Services\EventSeriesManager
        arguments:
            - "@doctrine.orm.entity_manager"
            - "EventSeriesBundle:EventSeries"
            - "@event_dispatcher"
            - "@logger"
    //*/
    
    protected $em;
    protected $dispatcher;
    protected $logger;
    protected $repo;

    protected $class;

    /**
     * BaseEntityManager constructor.
     * @param EntityManager             $em
     * @param                           $class
     * @param EventDispatcherInterface  $dispatcher
     * @param LoggerInterface           $logger
     */
    public function __construct($em, $class, $dispatcher, $logger) {
        $this->em           = $em;
        $this->repo         = $this->em->getRepository($class);
        $this->dispatcher   = $dispatcher;
        $this->logger       = $logger;

        $metadata       = $this->em->getClassMetadata($class);
        $this->class    = $metadata->getName();

        // todo: alert logger that manager has been created
    }

    public function createNew ($persist = true) {
        // instantiate
        $object = new $this->class();

        // persist?
        if ($persist) {
            $this->em->persist($object);
        }

        $persistStatus = ($persist) ? 'true' : 'false';
        $this->logger->info('Create new '. $this->class .' obj. Persist is ('. $persistStatus .')');

        return $object;
    }

    public function getAllBy($criteriaArray) {
        $result = $this->repo->findBy(
            $criteriaArray
        );
        return $result;
    }

    public function getOneBy($criteriaArray) {
        $result = $this->repo->findBy(
            $criteriaArray
        );
        return $result;
    }
}
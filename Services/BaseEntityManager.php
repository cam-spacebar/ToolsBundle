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
        $newObj = parent::createNew(false);

        // configure
        // ...

        if ($persist) {
            $this->persist($newObj);
        }

        // log
        $persistStatus = ($persist) ? 'true' : 'false';
        $string = 'Created a new '. $this->class .' obj. Persist ('. $persistStatus .').';
        // custom notes
        $newObj =. ' with question caption:  "'.
            $question->getQuestionCaption() .'", for event series: "'.
            $eventSeries->getName() .'"'.

        $this->logString ($string);

        return $newObj;
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

    // used to output result when using Doctrine fixtures > set to true when in LoadUserData.php (or other fixtures file)
    protected $outputLogToScreen;

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
        $result = $this->repo->findOneBy(
            $criteriaArray
        );
        return $result;
    }

    public function getOneById($id) {
        $result = $this->repo->findOneBy(
            array ('id'        => $id)
        );
        return $result;
    }

    /**
     * @return mixed
     */
    public function getOutputLogToScreen()
    {
        return $this->outputLogToScreen;
    }

    /**
     * @param mixed $outputLogToScreen
     * used to output result when using Doctrine fixtures > set to true when in LoadUserData.php (or other fixtures file)
     */
    public function setOutputLogToScreen($outputLogToScreen)
    {
        $this->outputLogToScreen = $outputLogToScreen;
    }

    public function logString ($logString) {
        $this->logger->info($logString);

        if ($this->outputLogToScreen) {
            // used to output result when using Doctrine fixtures
            print $logString ."\n";
        }

        return true;
    }

    // use this instead of direct em call to apply the law of demeter
    // (may want to use a different persistence layer at some point)
    public function persist ($obj) {
        $this->em->persist($obj);
    }
}
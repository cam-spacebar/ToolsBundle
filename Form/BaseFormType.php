<?php

namespace VisageFour\Bundle\ToolsBundle\Form;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractType;

class BaseFormType extends AbstractType
{
    /* implementation:
    TYPE CLASS:
    public function __construct(EntityManager $em, EventDispatcherInterface $dispatcher, LoggerInterface $logger) {
        parent::__construct($em, $dispatcher, $logger);
    }

    SERVICE DEFINITION:
    twencha.xxxx_form:
        class: Twencha\Bundle\EventRegistrationBundle\Form\xxxx
        arguments:
            - "@doctrine.orm.entity_manager"
            - "@event_dispatcher"
            - "@logger"
    // */

    protected $em;
    protected $dispatcher;
    protected $logger;

    public function __construct(EntityManager $em, EventDispatcherInterface $dispatcher, LoggerInterface $logger) {
        $this->em               = $em;
        $this->dispatcher       = $dispatcher;
        $this->logger           = $logger;
    }
}
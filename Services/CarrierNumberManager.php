<?php

namespace VisageFour\Bundle\ToolsBundle\Services;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use VisageFour\Bundle\ToolsBundle\Entity\Code;

class CarrierNumberManager extends BaseEntityManager {
    /**
     * CarrierNumberManager constructor.
     * @param EntityManager $em
     * @param $class
     * @param EventDispatcherInterface $dispatcher
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManager $em, $class, EventDispatcherInterface $dispatcher, LoggerInterface $logger) {
        parent::__construct($em, $class, $dispatcher, $logger);
    }

    function getCarrierNumberByNumber ($number) {
        $number = $this->normalizeMobileNumber ($number);
        $response = $this->repo->findOneBy (array(
            'number'  => $number
        ));

        return $response;
    }

    // returns a number with a leading +61 (if in australia?)
    function normalizeMobileNumber ($number) {
        return $number;
    }
}
<?php

namespace VisageFour\Bundle\ToolsBundle\Services;

use Doctrine\ORM\EntityManager;
use Platypuspie\AnchorcardsBundle\Entity\CarrierNumber;
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

    /**
     * @param $number
     * @return null|CarrierNumber
     */
    function getCarrierNumberByNumber ($number, $throwError = false) {
        $number = $this->normalizeMobileNumber ($number);
        $response = $this->repo->findOneBy (array(
            'number'  => $number
        ));

        if ($throwError) {
            if (empty($carrierNumber)) {
                throw new \Exception('could not find Carrier Number with number: "'. $number .'"');
            }
        }

        return $response;
    }
    function getCarrierNumberByReference ($reference, $throwError = false) {
        $response = $this->repo->findOneBy (array(
            'reference'  => $reference
        ));

        if ($throwError) {
            if (empty($carrierNumber)) {
                throw new \Exception('could not find Carrier Number with: "'. $reference .'"');
            }
        }

        return $response;
    }

    // returns a number with a leading +61 (if in australia?)
    function normalizeMobileNumber ($number) {
        return $number;
    }
}
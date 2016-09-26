<?php

namespace VisageFour\Bundle\ToolsBundle\Services;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use VisageFour\Bundle\ToolsBundle\Entity\Code;

class CodeManager extends BaseEntityManager {
    /**
     * CodeManager constructor.
     * @param EntityManager $em
     * @param $class
     * @param EventDispatcherInterface $dispatcher
     * @param LoggerInterface $logger
    */
    public function __construct(EntityManager $em, $class, EventDispatcherInterface $dispatcher, LoggerInterface $logger) {
        parent::__construct($em, $class, $dispatcher, $logger);
    }

    public function createNew ($persist = true, $codeNumber = null) {
        // instantiate
        /** @var Code $code */
        $code = parent::createNew(false);

        // configure
        if (empty($codeNumber)) {
            // generated randomized code string
            $codeNumber = $this->createUniqueCodeStr ();
        }
        $code->setCode ($codeNumber);

        // persist
        if ($persist) {
            $this->em->persist($code);
        }

        return $code;
    }

    function getCodeByCode ($code) {
        $response = $this->repo->findOneBy (array(
            'code'  => $code
        ));

        return $response;
    }

    // todo: refactor this for objects not array elements and allow to persist
    public function buildUniqueCodes ($existingCodes, $numOfCodes = 400) {
        /*
        $newUniqueCodes = array ();
        for ($i = 1; $i <= $numOfCodes; $i++) {
            $newUniqueCodes [] = $this->makeNewUniqueCode ($existingCodes, $newUniqueCodes);
        }

        return $newUniqueCodes;
        */
    }

    // todo: refactor methods referenceing this to instead use the factory method instead.
    public function createUniqueCodeObj ($persist = true) {
        $newCodeStr = $this->createUniqueCodeStr ();
        $newCode    = $this->createNew($newCodeStr);

        if ($persist) {
            $this->em->persist($newCode);
            $this->em->flush();
        }

        return $newCode;
    }

    // continue looping until found a unique code
    public function createUniqueCodeStr () {
        $newCodeStr     = $this->createRandomCode (3, 3);

        if (!empty($this->getCodeByCode($newCodeStr))) {
            $newCodeStr = $this->createUniqueCodeStr();
        }
        return $newCodeStr;
    }

    public function createRandomCode ($noOfChrs = 3, $noOfNums = 3) {
        $response = '';
        for ($i = 0; $i < $noOfChrs; $i++) {
            $curVal = rand(0, 25);
            $response .= chr (97 + (int) $curVal);
        }

        for ($j = 0; $j < $noOfNums; $j++) {
            $curVal = rand(0, 9);
            $response .= (int) $curVal;
        }

        return $response;
    }
}
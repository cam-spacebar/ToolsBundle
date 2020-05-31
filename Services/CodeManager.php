<?php

namespace VisageFour\Bundle\ToolsBundle\Services;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use VisageFour\Bundle\ToolsBundle\Entity\Code;
use VisageFour\Bundle\ToolsBundle\Repository\CodeRepository;

/*
 * You must extend this class, it must stay abstract, if it's concrete,
 * then there's issues with the return type of getRepo()
 */
abstract class CodeManager extends BaseEntityManager {
    /**
     * CodeManager constructor.
     * @param EntityManager $em
     * @param $class
     * @param EventDispatcherInterface $dispatcher
     * @param LoggerInterface $logger
    */
    public function __construct(EntityManager $em, $class, EventDispatcherInterface $dispatcher, LoggerInterface $logger, LoggingExtraData $loggingExtraData) {
        parent::__construct($em, $class, $dispatcher, $logger, $loggingExtraData);
    }

    /**
     * @param bool $persist
     * @param null $codeNumber
     * @return mixed|Code
     * @throws \Doctrine\ORM\ORMException
     *
     * Check the DB to see if the code is already used.
     */
    public function createNew ($flush = true, $codeNumber = null) {
        // instantiate
        /** @var Code $code */
        $code = parent::createNew(false, false);

        // configure
        if (empty($codeNumber)) {
            // generated randomized code string
            $codeNumber = $this->createUniqueCodeStr ();
        }
        $code->setCode ($codeNumber);

        $this->em->persist($code);
        if ($flush) {
            $this->flush->persist($code);    // this will load the obj into the database so the code isn't duplicated on the next loop accidentally
        }

        $this->logObjCreation($code);

        return $code;
    }

    function getCodeByCode ($code) {
        $response = $this->repo->findOneBy (array(
            'code'  => $code
        ));

        return $response;
    }

    public function bulkBuildCodes ($numOfCodes) {
        $newUniqueCodes = array ();
        for ($i = 1; $i <= $numOfCodes; $i++) {
            $newUniqueCodes = $this->createNew (true);
        }

        return $newUniqueCodes;
    }

    // continue looping until found a unique code
    public function createUniqueCodeStr ($curLayersDeep = 1) {
        $newCodeStr     = $this->createRandomCode (3, 3);

        $maxAllowedLayersDeep = 1000;
        if ($curLayersDeep > $maxAllowedLayersDeep) {
            throw new \Exception('matching too many duplicate code strings in the DB when trying to find a new unique code. Now past maximum layers deep (duplicates) of '. $maxAllowedLayersDeep .' allowed.');
            // todo: send an email to the app admin also!
        }

        // test if code is unique, if not, try generating a new code again (go one "layer deeper")
        if (!empty($this->getCodeByCode($newCodeStr))) {
            $newCodeStr = $this->createUniqueCodeStr($curLayersDeep+1);
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

    public function buildUniqueCodes ($existingCodes, $numOfCodes = 400) {
        throw new \Exception('please use bulkBuildCodes() instead');
    }
}
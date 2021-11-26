<?php

namespace VisageFour\Bundle\ToolsBundle\Services;

use VisageFour\Bundle\ToolsBundle\Services\CodeGenerator;
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

    private $alphaNumbericMapping;
    /**
     * @var CodeGenerator
     */
    private $codeGenerator;

    /**
     * CodeManager constructor.
     * @param EntityManager $em
     * @param $class
     * @param EventDispatcherInterface $dispatcher
     * @param LoggerInterface $logger
    */
    public function __construct(EntityManager $em, $class, EventDispatcherInterface $dispatcher, LoggerInterface $logger, CodeGenerator $codeGenerator) {
        parent::__construct($em, $class, $dispatcher, $logger);
        $this->codeGenerator = $codeGenerator;
    }

    /**
     * Create a blankbadge
     * Check the DB to see if the code is already used.
     *
     * @param bool $persist
     * @param null $codeNumber
     * @return mixed|Code
     * @throws \Doctrine\ORM\ORMException
     *
     */
    public function createNew ($flush = true, $logObjCreation = true, $codeNumber = null, $codeGenStrat = Code::CODE_GEN_STRAT_BASIC) {
        // instantiate
        /** @var Code $code */
        $code = parent::createNew(false, false);

        // configure
        if (empty($codeNumber)) {
            // generated randomized code string
            $codeNumber = $this->createUniqueCodeStr ($codeGenStrat);
        }
        $code->setCode ($codeNumber);

        $this->em->persist($code);
        if ($flush) {
            $this->em->flush($code);    // this will load the obj into the database so the code isn't duplicated on the next loop accidentally
        }

        if ($logObjCreation) {
           $this->logObjCreation($code);
        }

        return $code;
    }

    function getCodeByCode ($code) {
        $response = $this->repo->findOneBy (array(
            'code'  => $code
        ));

        return $response;
    }

    public function bulkBuildCodes ($numOfCodes, $codeGenStrat = Code::CODE_GEN_STRAT_BASIC) {
        $newUniqueCodes = array ();
        for ($i = 1; $i <= $numOfCodes; $i++) {
            $newUniqueCodes = $this->createNew (true, false, null, $codeGenStrat);
            throw new \Exception('what about duplicates?');
        }

        return $newUniqueCodes;
    }

    // continue looping until found a unique code
    private function createUniqueCodeStr ($codeGenStrat, $curLayersDeep = 1) {
        switch ($codeGenStrat) {
            case CODE::CODE_GEN_STRAT_BASIC:
                $newCodeStr     = $this->codeGenerator->createRandomCode(3, 3);
                break;
            case CODE::CODE_GEN_STRAT_RAND_ALPHA_NUMBERIC:
                $newCodeStr = $this->codeGenerator->genAlphaNumericCode(32);
                break;
            default:
                throw new \Exception(
                    'code generation strategy flag with value: '. $codeGenStrat .' is not recognized.'
                );
        }

        $maxAllowedLayersDeep = 1000;
        if ($curLayersDeep > $maxAllowedLayersDeep) {
            throw new \Exception('matching too many duplicate code strings in the DB when trying to find a new unique code. Now past maximum layers deep (duplicates) of '. $maxAllowedLayersDeep .' allowed.');
            // todo: send an email to the app admin also!
        }

        // test if code is unique, if not, try generating a new code again (go one "layer deeper")
        if (!empty($this->getCodeByCode($newCodeStr))) {
            $newCodeStr = $this->createUniqueCodeStr($codeGenStrat, $curLayersDeep+1);
        }
        return $newCodeStr;
    }

    public function buildUniqueCodes ($existingCodes, $numOfCodes = 400) {
        throw new \Exception('please use bulkBuildCodes() instead');
    }
}
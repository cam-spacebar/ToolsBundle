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

    private $alphaNumbericMapping;
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
            $this->flush->persist($code);    // this will load the obj into the database so the code isn't duplicated on the next loop accidentally
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
        }

        return $newUniqueCodes;
    }

    // continue looping until found a unique code
    public function createUniqueCodeStr ($codeGenStrat, $curLayersDeep = 1) {
        switch ($codeGenStrat) {
            case CODE::CODE_GEN_STRAT_BASIC:
                $newCodeStr = $this->createRandomCode(3, 3);
                break;
            case CODE::CODE_GEN_STRAT_RAND_ALPHA_NUMBERIC:
                $newCodeStr = $this->genAlphaNumericCode(32);
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

    private function genAlphaNumericCode (int $noOfChars) {
        $ANmapping = $this->getAlphaNumericMapping();
        $newCode = '';
        $noOfChars = 20;

        for ($i=1; $i <= $noOfChars; $i++) {
            $curVal = rand(1, 34);
//            print $curVal .'-';
            $newCode .= $ANmapping[$curVal];
        }
//        dd($newCode);
//        dump(strlen($newCode));
//        dump( is_string($newCode));
//        dd($newCode);

        return $newCode;
    }

    private function getAlphaNumericMapping () {
        if (empty($this->alphaNumbericMapping)) {

            $this->alphaNumbericMapping = array (
                '1'             => 1,
                '2'             => 2,
                '3'             => 3,
                '4'             => 4,
                '5'             => 5,
                '6'             => 6,
                '7'             => 7,
                '8'             => 8,
                '9'             => 9,
                '10'             => 9,
                'a'             => 'a',
                'b'             => 'b',
                '11'             => 'c',
                '12'             => 'd',
                '13'             => 'e',
                '14'             => 'f',
                '15'             => 'g',
                '16'             => 'h',
                '17'             => 'i',
                '18'             => 'j',
                '19'             => 'k',
                '20'             => 'l',
                '21'             => 'm',
                '22'             => 'n',
                '23'             => 'o',
                '24'             => 'p',
                '25'             => 'q',
                '26'             => 'r',
                '27'             => 's',
                '28'             => 't',
                '29'             => 'u',
                '30'             => 'v',
                '31'             => 'w',
                '32'             => 'x',
                '33'             => 'y',
                '34'             => 'z'
            );
        }

        return $this->alphaNumbericMapping;
    }
}
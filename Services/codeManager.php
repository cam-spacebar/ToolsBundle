<?php
/**
 * Created by PhpStorm.
 * User: cameronburns
 * Date: 30/01/2016
 * Time: 2:25 PM
 */

namespace VisageFour\ToolsBundle\Services;

use Doctrine\ORM\EntityManager;
use VisageFour\ToolsBundle\Entity\code;

class CodesQR {
    protected $em;
    protected $repo;

    public function __construct (EntityManager $em) {
        $this->em           = $em;
        $this->repo         = $this->em->getRepository('ToolsBundle:code');
    }

    function getCodeByCode ($code) {
        $response           = $this->repo->findOneBy (array(
            'code'            => $code
        ));

        return $response;
    }

    static function createCode ($codeNumber) {
        $response = new code();
        $response->setCode          ($codeNumber);

        return $response;
    }

    public function findOneBy ($parameters = NULL) {
        $curCode = $this->repo->findOneBy ($parameters);

        return $curCode;
    }

    public function findAllBy ($parameters = NULL) {
        $codes = $this->repo->findAll ($parameters);

        return $codes;
    }
}
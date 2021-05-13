<?php

namespace VisageFour\Bundle\ToolsBundle\Repository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use VisageFour\Bundle\ToolsBundle\Entity\BasePerson;

/**
 * BasePersonRepository
 *
 */
class BasePersonRepository extends ServiceEntityRepository
{
    public function doesPersonExist($email)
    {
        $person = $this->findOneByEmailCanonical($email);

        if (!empty($person)) {
            return true;
        }
        return false;
    }

    /**
     * Canonicalize email
     */
    public function findOneByEmailCanonical ($email) {
        $emailCanon = BasePerson::canonicalizeEmail($email);
        return $this->findOneBy(array(
            'emailCanonical'        => $emailCanon
        ));
    }
}
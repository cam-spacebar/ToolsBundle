<?php

namespace VisageFour\Bundle\ToolsBundle\Repository;
use App\Entity\Person;
use App\VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode\PersonNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use VisageFour\Bundle\ToolsBundle\Entity\BasePerson;
use VisageFour\Bundle\ToolsBundle\Traits\LoggerTrait;

/**
 * BasePersonRepository
 *
 */
class BasePersonRepository extends ServiceEntityRepository
{
    use LoggerTrait;

    public function __construct(ManagerRegistry $registry, $entityClassName = BasePerson::class)
    {
        // note: you must create a class that overrides this and passes in the correct $entityClassName parameter.
        // the commented out section below cannot be used:
//        parent::__construct($registry, BasePerson::class);

        parent::__construct($registry, $entityClassName);
    }

    public function doesPersonExist($email)
    {
        $person = $this->findOneByEmailCanonical($email, false);

        if (!empty($person)) {
            return true;
        }

        return false;
    }

    /**
     *
     */
    public function findOneByEmailCanonical ($email, $throwExceptionIfNotFound = true): Person {
        $this->logger->info('looking for person with email (in DB): '. $email);
        $emailCanon = BasePerson::canonicalizeEmail($email);

        $result = $this->findOneBy(array(
            'emailCanonical'        => $emailCanon
        ));

        if (empty($result) && $throwExceptionIfNotFound){
            throw new PersonNotFoundException($email);
        }

        $this->logger->info('found person with email: '. $email .': ', [$result]);
        return $result;
    }
}
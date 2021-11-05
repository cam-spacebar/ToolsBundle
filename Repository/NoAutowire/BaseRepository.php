<?php
/*
* created on: 24/10/2021 - 21:51
* by: cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Repository\NoAutowire;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use VisageFour\Bundle\ToolsBundle\Traits\LoggerTrait;
use VisageFour\Bundle\ToolsBundle\Traits\EntityManagerTrait;

class BaseRepository extends ServiceEntityRepository
{
    use LoggerTrait;
    use EntityManagerTrait;

    // when set to true, it will print the contents of the object to the console when an entity object is instantiated.
    // Use this for fixtures or for when writing tests.
    protected $outputValuesOnCreation = false;

    /**
     * @return bool
     */
    public function isOutputValuesOnCreation(): bool
    {
        return $this->outputValuesOnCreation;
    }

    /**
     * @param bool $outputValuesOnCreation
     */
    public function setOutputValuesOnCreation(bool $outputValuesOnCreation): void
    {
        $this->outputValuesOnCreation = $outputValuesOnCreation;
    }

    protected function persistAndLogEntityCreation($newObj, $persist = true)
    {
        $className = (new \ReflectionClass($newObj))->getShortName();
        $this->logger->info(
            'New entity created: '. $className,
            [$newObj]
        );

        if ($persist) {
            $this->persist($newObj);
        }

        if ($this->outputValuesOnCreation) {
            print "\nNew entity created: ";
            $newObj->outputContents();
        }
    }
}
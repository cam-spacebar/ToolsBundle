<?php
/*
* created on: 24/10/2021 - 21:51
* by: cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Repository\NoAutowire;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use VisageFour\Bundle\ToolsBundle\Traits\LoggerTrait;
use VisageFour\Bundle\ToolsBundle\Traits\EntityManagerTrait;
use Doctrine\Persistence\ManagerRegistry;

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
        if ($outputValuesOnCreation == true) {
            print "\n==== OUTPUT ENTITY VALUES ON CREATION = true (for: \"$this->_entityName\" Repository) ====";
        }

        $this->outputValuesOnCreation = $outputValuesOnCreation;
    }

    public function __construct (ManagerRegistry $registry, $class) {
        parent::__construct($registry, $class);
        $this->class = $class;
    }

    protected function persistAndLogEntityCreation($newObj, $persist = true)
    {
        $className = $newObj->getShortName();
        $this->logger->info(
            'New entity created: '. $className,
            [$newObj],
            'grey_bold'
        );

        if ($persist) {
            $this->persist($newObj);
        }

        if ($this->outputValuesOnCreation == true) {
            print "\n== New ". $className ." entity created: \n";
            $newObj->outputContents();
        }
    }

    public function removeAllInArray(\Traversable $entities)
    {
        /**
         * @var  $curI
         * @var  $curEntity
         */
        foreach($entities as $curI => $curEntity) {
//            dump($curOverlay);
//            $curOverlay->setRelatedTemplate(null);
//            $curOverlay->getRelatedTemplate()->removeRelatedImageOverlay($curOverlay);
            $this->em->remove($curEntity);
        }

        $this->em->flush();
    }

    public function findOneByIdOrException(int $id)
    {
        $entity = $this->findOneBy(
          ['id' => $id]
        );
//        dump($entity);
//        print "\nsd12w\n";
        if (empty($entity)) {
            throw new \Exception('Could not find '. $this->_entityName);
        }

        return $entity;
    }
}
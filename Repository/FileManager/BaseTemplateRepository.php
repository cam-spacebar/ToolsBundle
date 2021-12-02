<?php
/*
* created on: 25/11/2021 - 17:07
* by: Cameron
*/

namespace App\VisageFour\Bundle\ToolsBundle\Repository\FileManager;

use App\Entity\FileManager\File;
use App\Entity\FileManager\Template;
use App\Repository\FileManager\ImageOverlayRepository;
use Doctrine\Common\Collections\ArrayCollection;
use VisageFour\Bundle\ToolsBundle\Repository\NoAutowire\BaseRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Template|null find($id, $lockMode = null, $lockVersion = null)
 * @method Template|null findOneBy(array $criteria, array $orderBy = null)
 * @method Template[]    findAll()
 * @method Template[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BaseTemplateRepository extends BaseRepository
{


    /**
     * @var ImageOverlayRepository
     */
    private $overlayRepo;

    public function __construct (ManagerRegistry $registry, $class, ImageOverlayRepository $overlayRepo) {
        parent::__construct($registry, $class);
        $this->overlayRepo = $overlayRepo;
    }

    public function createNewTemplate (File $canvasFile)
    {
        $new = new Template($canvasFile);

        $this->persistAndLogEntityCreation($new);

        return $new;
    }

    /**
     * @param File $file
     *
     * Remove all template entities (and their sub ImageOverlay entities) from the DB (that belong to the provide $file entity).
     */
    public function removeAllInArray(\Traversable $templateEntities)
    {
//        dump($templateEntities);
        /**
         * @var  $curI
         * @var Template $curTemplate
         */
        foreach($templateEntities as $curI => $curTemplate) {
//            dd($templateEntities);
            // remove template from original file


            // delete all the imageOverlay entities
            $this->overlayRepo->removeAllInArray($curTemplate->getRelatedImageOverlays());
            $this->em->flush();
//            $curTemplate->setRelatedOriginalFile(null);

//            $this->em->remove($curTemplate);
        }
//        die('asdfasdfasdf');

        return true;
    }

    // /**
    //  * @return Template[] Returns an array of Template objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Template
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
<?php
/*
* created on: 26/11/2021 - 11:54
* by: Cameron
*/

namespace App\VisageFour\Bundle\ToolsBundle\Repository\FileManager;

use App\Entity\FileManager\ImageOverlay;
use App\Entity\FileManager\Template;
use VisageFour\Bundle\ToolsBundle\Repository\NoAutowire\BaseRepository;

/**
 * @method ImageOverlay|null find($id, $lockMode = null, $lockVersion = null)
 * @method ImageOverlay|null findOneBy(array $criteria, array $orderBy = null)
 * @method ImageOverlay[]    findAll()
 * @method ImageOverlay[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BaseImageOverlayRepository extends BaseRepository
{
    public function createNewOverlay (Template $template, int $posX, int $posY, int $w, int $h, string $labelName)
    {
        $new = new ImageOverlay($template, $posX, $posY, $w, $h, $labelName);

        $this->persistAndLogEntityCreation($new);

        return $new;
    }


    public function removeAllInArray(\Traversable $overlayEntities)
    {
        /**
         * @var  $curI
         * @var ImageOverlay $curOverlay
         */
        foreach($overlayEntities as $curI => $curOverlay) {
            $curOverlay->setRelatedTemplate(null);
            $curOverlay->getRelatedTemplate()->removeRelatedImageOverlay($curOverlay);
            $this->em->remove($curOverlay);
        }
    }


    // /**
    //  * @return ImageOverlay[] Returns an array of ImageOverlay objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ImageOverlay
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
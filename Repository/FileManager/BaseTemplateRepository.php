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
use VisageFour\Bundle\ToolsBundle\RepositoryAutowired\PrintAttribution\BatchRepository;

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

    /**
     * @var BatchRepository
     */
    private $batchRepo;

    public function __construct (ManagerRegistry $registry, $class, ImageOverlayRepository $overlayRepo, BatchRepository $batchRepo) {
        parent::__construct($registry, $class);
        $this->overlayRepo = $overlayRepo;
        $this->batchRepo = $batchRepo;
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
//            dump($curTemplate);
//            $curFile = $curTemplate->getRelatedOriginalFile();
//            $curFile->removeRelatedTemplate($curTemplate);

//            $curTemplate->setRelatedOriginalFile(null);

//            dd($templateEntities);
            // remove template from original file

            // delete all the imageOverlay entities
            $this->delete($curTemplate);
            // todo: delete / remove derivatives files too
        }
//        die('asdfasdfasdf');

        return true;
    }

    public function delete(Template $template)
    {
        $this->em->flush();
        $this->overlayRepo->removeAllInArray($template->getRelatedImageOverlays());

//        dump($template->getRelatedBatches());
        $this->batchRepo->removeAllInArray($template->getRelatedBatches());

        $this->em->remove($template);
        $this->em->flush();

    }
}
<?php
/*
* created on: 01/12/2021 - 13:25
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\RepositoryAutowired\PrintAttribution;

use Doctrine\Persistence\ManagerRegistry;
use VisageFour\Bundle\ToolsBundle\Entity\PrintAttribution\Batch;
use VisageFour\Bundle\ToolsBundle\Entity\PrintAttribution\TrackedFile;
use VisageFour\Bundle\ToolsBundle\Repository\NoAutowire\BaseRepository;
use VisageFour\Bundle\ToolsBundle\Services\FileManager\FileManager;

/**
 * @method TrackedFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method TrackedFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method TrackedFile[]    findAll()
 * @method TrackedFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrackedFileRepository extends BaseRepository
{
    /**
     * @var FileManager
     */
    private $fileManager;

    public function __construct(ManagerRegistry $registry, FileManager $fileManager)
    {
        parent::__construct($registry, TrackedFile::class);
        $this->fileManager = $fileManager;
    }

    public function createNewTrackedFile(Batch $batch, int $order, $status): TrackedFile
    {
        $new = new TrackedFile($batch, $order, $status);

        $this->persistAndLogEntityCreation($new);

        return $new;
    }

    public function removeAllInArray(\Traversable $entities)
    {
        /**
         * @var TrackedFile $curEntity
         */
        foreach($entities as $curI => $curEntity) {
            $this->delete($curEntity);
        }
        $this->em->flush();
    }

    public function delete(TrackedFile $trackedFile)
    {
        // delete file entity too - local, remote and DB record:
        $file = $trackedFile->getRelatedFile();
        if (empty($file)) {
            $this->logger->info('trackedFile (id: '. $trackedFile->getId() .') doesnt contain a file to delete. (delete command skipped)');

        } else {
            $this->fileManager->deleteFile($file);
        }

        $this->em->remove($trackedFile);
    }
}
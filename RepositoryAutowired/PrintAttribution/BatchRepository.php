<?php

namespace VisageFour\Bundle\ToolsBundle\RepositoryAutowired\PrintAttribution;

use App\Entity\FileManager\Template;
use VisageFour\Bundle\ToolsBundle\Entity\PrintAttribution\Batch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use VisageFour\Bundle\ToolsBundle\Repository\NoAutowire\BaseRepository;

/**
 * @method Batch|null find($id, $lockMode = null, $lockVersion = null)
 * @method Batch|null findOneBy(array $criteria, array $orderBy = null)
 * @method Batch[]    findAll()
 * @method Batch[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BatchRepository extends BaseRepository
{
    /**
     *  @var int
     * this records the last batchNo. If get BatchNo used again and produces the same result, we know that we have a duplicate batch no
     * (likely because ->flush() wasn't called).
     */
    private $lastUsedBatchNo;
    /**
     * @var TrackedFileRepository
     */
    private $trackedFileRepo;

    public function __construct(ManagerRegistry $registry, TrackedFileRepository $trackedFileRepo)
    {
        parent::__construct($registry, Batch::class);
        $this->trackedFileRepo = $trackedFileRepo;
    }

    public function createNewBatch(Template $template, array $payload)
    {
        // get next "batchNo" in the series
        $batchNo = $this->getNextBatchNo($template);

        $new = new Batch($template, $payload, $batchNo);

        $this->persistAndLogEntityCreation($new, true);
        $new->setPayload($payload);

        return $new;
    }

    public function getNextBatchNo(Template $template)
    {
        $query = $this->createQueryBuilder('b')
            ->select('MAX(b.batchNo)')
            ->join('b.relatedTemplate', 't')
//            ->join('EventRegistrationBundle:QuestionOption', 'qo', 'WITH', 'a.relatedQuestionOption = qo.id')
//            ->where('qo.id = :langQOId')
            ->where('t.id = :templateId')
            ->setParameter('templateId', $template->getId())
            ->getQuery()
//            ->getSQL()
        ;

//        dump('highest: ', $highest);
        $highest = $query->getSingleScalarResult();

        if (empty($highest)) {
            $newBatchNo = 1;
        } else {
            $newBatchNo = $highest + 1;
        }

        if ($newBatchNo == $this->lastUsedBatchNo) {
            throw new \Exception ('new batchNo: '. $newBatchNo .' has already been returned. You need to call flush() between batchNo generations.');
        }
        $this->lastUsedBatchNo = $newBatchNo;


        $this->logger->info('new BatchNo: '. $newBatchNo, [], 'red' );


        return $newBatchNo;
    }

    public function removeAllInArray(\Traversable $entities)
    {
        /**
         * @var  $curI
         * @var Batch $curEntity
         */
        foreach($entities as $curI => $curEntity) {
//            dump($curOverlay);
//            $curOverlay->setRelatedTemplate(null);
//            $curOverlay->getRelatedTemplate()->removeRelatedImageOverlay($curOverlay);
            $this->delete($curEntity);
        }
        $this->em->flush();
    }

    public function delete(Batch $batch)
    {
        $this->logger->info('deleting Batch with id: '. $batch->getId());

        $trackedFiles = $batch->getTrackedFiles();
        $this->trackedFileRepo->removeAllInArray($trackedFiles);

        $this->em->remove($batch);
    }
}
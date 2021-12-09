<?php
/*
* created on: 30/11/2021 - 13:34
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Services\Image;

use App\Entity\FileManager\File;
use App\Entity\FileManager\ImageOverlay;
use App\Entity\FileManager\Template;
use App\Repository\FileManager\FileRepository;
use App\Repository\FileManager\ImageOverlayRepository;
use App\Repository\FileManager\TemplateRepository;
use Doctrine\ORM\EntityManager;
use VisageFour\Bundle\ToolsBundle\Classes\ImageOverlay\Image;
use VisageFour\Bundle\ToolsBundle\Entity\PrintAttribution\TrackedFile;
use VisageFour\Bundle\ToolsBundle\Interfaces\FileManager\BaseFileInterface;
use VisageFour\Bundle\ToolsBundle\RepositoryAutowired\PrintAttribution\BatchRepository;
use VisageFour\Bundle\ToolsBundle\RepositoryAutowired\PrintAttribution\TrackedFileRepository;
use VisageFour\Bundle\ToolsBundle\Services\FileManager\FileManager;
use VisageFour\Bundle\ToolsBundle\Services\QRcode\QRCodeGenerator;
use VisageFour\Bundle\ToolsBundle\Services\UrlShortener\UrlShortenerHelper;
use VisageFour\Bundle\ToolsBundle\Traits\LoggerTrait;

/**
 * Class OverlayManager
 * @package App\VisageFour\Bundle\ToolsBundle\Services\OverlayTemplate
 *
 * Manages creating image templates and overlays to allow for programmatic generation of image overlays (aka "composites").
 * This was originally created to facilitate the placement of QR codes onto advertising poster designs.
 *
 * Also allows newly created images / PDFs to be uploaded to AWS S3
 */
class OverlayManager
{
    use LoggerTrait;

    /**
     * @var TemplateRepository
     */
    private $templateRepo;

    /**
     * @var ImageOverlayRepository
     */
    private $overlayRepo;

    /**
     * @var ImageManipulation
     */
    private $imageManipulation;
    /**
     * @var EntityManager
     */
    private $em;
    /**
     * @var FileManager
     */
    private $fileManager;
    /**
     * @var UrlShortenerHelper
     */
    private $urlShortenerHelper;
    /**
     * @var TrackedFileRepository
     */
    private $trackedFileRepo;
    /**
     * @var BatchRepository
     */
    private $batchRepository;
    /**
     * @var FileRepository
     */
    private $fileRepo;

    public function __construct(TemplateRepository $templateRepo, ImageOverlayRepository $overlayRepo, ImageManipulation $imageManipulation, EntityManager $em, FileManager $fileManager, UrlShortenerHelper $urlShortenerHelper, TrackedFileRepository $trackedFileRepo, BatchRepository $batchRepository, FileRepository $fileRepo)
    {
        $this->templateRepo         = $templateRepo;
        $this->overlayRepo          = $overlayRepo;
        $this->imageManipulation    = $imageManipulation;
        $this->em                   = $em;
        $this->fileManager          = $fileManager;
        $this->urlShortenerHelper   = $urlShortenerHelper;
        $this->trackedFileRepo      = $trackedFileRepo;
        $this->batchRepository      = $batchRepository;
        $this->fileRepo = $fileRepo;
    }

    /**
     * @param File $canvasFile
     * @param int $posX
     * @param int $posY
     * @param int $w
     * @param int $h
     * @param string $labelName
     * @return Template
     *
     * Creates a template entity and an overlay entity.
     */
    public function createNewTemplateAndOverlay(File $canvasFile, int $posX, int $posY, int $w, int $h, string $labelName): Template
    {
        // create template and imageOverlay
        $template = $this->templateRepo->createNewTemplate(
            $canvasFile
        );

        // create overlay
        $overlay = $this->overlayRepo->createNewOverlay(
            $template,
            $posX,
            $posY,
            $w,
            $h,
            $labelName
        );

        $template->addRelatedImageOverlay($overlay);
        $overlay->setRelatedTemplate($template);

        return $template;
    }

    /**
     * @param File $imageFile
     * @param Template $template
     * @param array $payload
     *
     * Use the ImageOverlay and Template entities to create a composite image/PDF that places the "overlay" (e.g. QR code with short URL) onto the provided $canvas File entity
     * then save/persist the new composite image to storage (i.e. AWS S3) as a File entity
     *
     */
    public function createCompositeImage(Template $template, array $payload): File
    {
        $this->logger->info('Creating composite image from template.', [$template, $payload]);
        $canvas = $template->getRelatedOriginalFile();

//        $trackedFile = new TrackedFile($canvas, $);

        // loop through ImageOverlay entities and apply them to the canvas
        $overlays = $template->getRelatedImageOverlays();
        $i = 0;
        foreach($overlays as $curI => $curOverlay) {
            $i++;
            if ($i > 1) {
                throw new \Exception('this code cant yet handle more than 1 overlay, please update. see: ->overlayImage() needs ouput feedback in.');
            }
            $QRCodeContents = $this->getCurrentPayload($payload, $curOverlay);

            // generate the QR code
            $overlayPathname = $this->urlShortenerHelper->generateShortUrlQRCodeFromURL($QRCodeContents);
            $overlayImg = new Image($overlayPathname);

            // generate the composite (with QR code).
            $composite = $this->imageManipulation->overlayImage (
                $canvas->getImageGD(),
                $overlayImg,
                $curOverlay->getXCoord(),
                $curOverlay->getYCoord(),
                $curOverlay->getWidth(),
                $curOverlay->getHeight()
            );
        }

//        $baseDir = 'src/VisageFour/Bundle/ToolsBundle/Tests/TestFiles/Image/';

        // save composite to local filesystem
        $tempFilename = 'composite_'. uniqid() .'.png';     // use uniqid() here to prevent wasted ->has() call to AWS S3.
        $filePath = "var/composites/temp/". $tempFilename;
        $this->imageManipulation->saveImage($composite, $filePath);

        // save the file to remote storage (AWS S3)
        $remoteSubFolder = 'composites/QRcoded';
        $composite = $this->fileManager->persistFile($filePath, $remoteSubFolder);
        $composite->setRelatedOriginalFile($canvas);
        $canvas->addRelatedDerivativeFile($composite);

        return $composite;
    }

    /**
     * @param TrackedFile $trackedFile
     */
    public function createCompositeImageByTrackedFile (TrackedFile $trackedFile): BaseFileInterface
    {
        if ($trackedFile->getStatus() == TrackedFile::STATUS_GENERATED) {
            throw new \Exception('TrackedFile (with id: '. $trackedFile->getId() .') has already been generated. It does not need to be generated again.');
        }

        $batch = $trackedFile->getRelatedBatch();
        $template = $batch->getRelatedTemplate();

        // create the composite and store it.
        $composite = $this->createCompositeImage($template, $batch->getPayload());

        $trackedFile->setRelatedFile($composite);
        $composite->setRelatedTrackedFile($trackedFile);

        $trackedFile->setStatus(TrackedFile::STATUS_GENERATED);

        $this->em->flush();

        return $composite;
    }

    /**
     * @param int $count
     * @param Image $canvas
     * @param Template $template
     * @param array $payload
     * @param bool $generateImmediately
     *
     * Create a new Batch entity and TrackedFile entities (for each composite that is to be created).
     * note: $generateImmediately = false will delay the creation (and upload to storage) of composites for a later stage
     */
    public function createNewBatch(int $count, BaseFileInterface $canvas, Template $template, array $payload, $generateImmediately = true)
    {
        $batch = $this->batchRepository->createNewBatch($template, $payload);

        $this->em->persist($batch);
        $this->em->flush();         // save batch to DB here, because if new batch created, it will throw exception due to duplicate batchNo

//        work from here: create each of the trackedfile - ready for rendering.
        $startNo = 1;
        $endNo = ($count+$startNo);
        for($i = $startNo; $i < $endNo; $i++) {
            $this->logger->sectionHeader('New Tracked File: '. $i .' [Batch: '. $batch->getBatchNo() .']');
            $curTrackedFile = $this->trackedFileRepo->createNewTrackedFile($batch, $i, TrackedFile::STATUS_IN_QUEUE);
//            dump($curTrackedFile);
            $batch->addTrackedFile($curTrackedFile);

            if ($generateImmediately) {
                $this->createCompositeImageByTrackedFile($curTrackedFile);
                $this->updateCompositeOriginalbasename($curTrackedFile->getRelatedFile(), $i);
            }
        }

        return $batch;
    }

    /**
     * @param BaseFileInterface $composite
     * @param $itemNo
     *
     * renames composite originalBasename to format: "FF A4 Flyer_[batch_G-014].png"
     */
    private function updateCompositeOriginalBasename(BaseFileInterface $composite, $itemNo)
    {
        // update originalFilename
//        $newFilename = 'composite_of_'. $canvas->getOriginalBasename();

        $canvas = $composite->getRelatedOriginalFile();
        $canvasFilename = $canvas->getOriginalFilename();
        $canvasExt = $canvas->getFileExtension();

        $batchId = $composite->getRelatedTrackedFile()->getRelatedBatch()->getBatchNo();
        $itemNo = sprintf('%03d', $itemNo);
        $newBasename = $canvasFilename. ' [batch_'. $batchId .'-'. $itemNo .'].'. $canvasExt;
        $composite->setOriginalFilename($newBasename);

        $logMsg = 'updated composite "originalFilename" to: "'. $newBasename .'"';
        $this->logger->info($logMsg, [], 'orange');
    }

    /**
     * @param array $payload
     * @param ImageOverlay $overlay
     *
     * Look for the $overlay->labelName in payload and return the its value.
     * throw exception if it cannot be found.
     */
    private function getCurrentPayload(array $payload, ImageOverlay $overlay)
    {
        $name = $overlay->getLabelName();
        if (empty($payload[$name])) {
            throw new \Exception('there is no value in the $payload for imageOverlay with labelName: '. $name);
        }

        return $payload[$name];
    }

    /**
     * @param File $file
     * This deletes the file entity (an image or PDF), it's template entity, overlay entities
     *
     */
    public function deleteFile(File $file)
    {
//        $file->setRelatedTemplate(null);
//        dd($file->getRelatedTemplates());
        $this->templateRepo->removeAllInArray($file->getRelatedTemplates());

        $this->em->flush();

        // todo: remove all URLs and hits?
        $this->fileRepo->removeAllInArray($file->getRelatedDerivativeFiles());
        $this->fileManager->deleteFile($file);
    }

    /**
     * delete all files in the DB: DB record, local and remote files - used for cleanup
     */
    public function deleteAllFiles($areYouSure = false)
    {
        if (!$areYouSure) {
            throw new \Exception ('please set $areYouSure = true to deleteAllFiles()');
        }

        $files = $this->fileRepo->findAll();
        /**
         * @var File $curFile
         */
        foreach ($files as $curI => $curFile) {
            // composites will be deleted as a chain-delete event when deleting templates / batch entities for the "master" canvas file.
            // in this case, don't try to "re-delete" the same file.
            $alreadyDeleted = !$this->em->contains($curFile);
            if (!$alreadyDeleted) {

                $this->deleteFile($curFile);
            }
        }

        return true;
    }
}
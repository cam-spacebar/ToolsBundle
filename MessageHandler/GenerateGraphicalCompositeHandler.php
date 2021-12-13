<?php
/*
* created on: 10/12/2021 - 15:37
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\MessageHandler;

use App\Entity\FileManager\ImageOverlay;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use VisageFour\Bundle\ToolsBundle\Classes\ImageOverlay\Image;
use VisageFour\Bundle\ToolsBundle\Entity\PrintAttribution\TrackedFile;
use VisageFour\Bundle\ToolsBundle\Message\GenerateGraphicalComposite;
use VisageFour\Bundle\ToolsBundle\Services\FileManager\FileManager;
use VisageFour\Bundle\ToolsBundle\Services\Image\ImageManipulation;
use VisageFour\Bundle\ToolsBundle\Services\Image\OverlayManager;
use VisageFour\Bundle\ToolsBundle\Services\UrlShortener\UrlShortenerHelper;
use VisageFour\Bundle\ToolsBundle\Traits\LoggerTrait;

class GenerateGraphicalCompositeHandler implements MessageHandlerInterface
{
    use LoggerTrait;

    /**
     * @var ImageManipulation
     */
    private $imageManipulation;

    /**
     * @var FileManager
     */
    private $fileManager;

    /**
     * @var UrlShortenerHelper
     */
    private $urlShortenerHelper;

    /**
     * @var EntityManager
     */
    private $em;
    /**
     * @var OverlayManager
     */
    private $overlayManager;

    public function __construct(ImageManipulation $imageManipulation, FileManager $fileManager, UrlShortenerHelper $urlShortenerHelper, EntityManager $em, OverlayManager $overlayManager)
    {
        $this->imageManipulation    = $imageManipulation;
        $this->fileManager          = $fileManager;
        $this->urlShortenerHelper   = $urlShortenerHelper;
        $this->em                   = $em;
        $this->overlayManager       = $overlayManager;
    }

    /**
     * @param GenerateGraphicalComposite $msg
     * @return \App\Entity\FileManager\File
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * behaviour:
     * generate a new composite image.
     * if the AWS File entity is marked as "to delete" or "deleted" - do nothing.
     * todo: add $force - to allow replacement of composite (as payload may change? ).
     */
    public function __invoke(GenerateGraphicalComposite $msg)
    {
        $template = $msg->getTemplate();
        $payload = $msg->getPayload();
        $trackedFile = $msg->getTrackedFile();

        if (!$this->checkStatusIsAcceptable($trackedFile)) {
            // if status is not acceptable, do not generate a composite
            return true;
        }

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

        // update TrackedFile
        $trackedFile->setRelatedFile($composite);
        $composite->setRelatedTrackedFile($trackedFile);
        
//        delete files that are generated asynronosely
//        - use "flagged for delete" flags.
//        - setup transports for prod (and sync for dev).

        $trackedFile->setStatus(TrackedFile::STATUS_GENERATED);

        $this->overlayManager->updateCompositeOriginalbasename(
            $trackedFile->getRelatedFile(),
            $trackedFile->getOrderNo()
        );

        $this->em->flush();

        return true;
    }

    // check the composite hasn't been generated already, or has (or will be) deleted.
    private function checkStatusIsAcceptable (TrackedFile $trackedFile) {
        $status = $trackedFile->getStatus();
        if ($status == TrackedFile::MARKED_FOR_DELETION || $status == TrackedFile::DELETED) {
            // do nothing.
            $this->logger->info('Not generating composite for tracked file: {id} as its status is: {status}.', $trackedFile);
            return false;
        }

        if ($status == TrackedFile::STATUS_GENERATED) {
            $msg = 'Tracked file with id {id} has already been generated. Will not generate again.';
            $this->logger->alert($msg, $trackedFile);
            return false;
        }

        return true;
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
}
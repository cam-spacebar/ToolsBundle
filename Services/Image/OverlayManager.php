<?php
/*
* created on: 30/11/2021 - 13:34
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Services\Image;

use App\Entity\FileManager\File;
use App\Entity\FileManager\ImageOverlay;
use App\Entity\FileManager\Template;
use App\Repository\FileManager\ImageOverlayRepository;
use App\Repository\FileManager\TemplateRepository;
use Doctrine\ORM\EntityManager;
use VisageFour\Bundle\ToolsBundle\Classes\ImageOverlay\Image;
use VisageFour\Bundle\ToolsBundle\Entity\PrintAttribution\TrackedFile;
use VisageFour\Bundle\ToolsBundle\RepositoryAutowired\PrintAttribution\TrackedFileRepository;
use VisageFour\Bundle\ToolsBundle\Services\FileManager\FileManager;
use VisageFour\Bundle\ToolsBundle\Services\QRcode\QRCodeGenerator;
use VisageFour\Bundle\ToolsBundle\Services\UrlShortener\UrlShortenerHelper;

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

    public function __construct(TemplateRepository $templateRepo, ImageOverlayRepository $overlayRepo, ImageManipulation $imageManipulation, EntityManager $em, FileManager $fileManager, UrlShortenerHelper $urlShortenerHelper, TrackedFileRepository $trackedFileRepo)
    {
        $this->templateRepo         = $templateRepo;
        $this->overlayRepo          = $overlayRepo;
        $this->imageManipulation    = $imageManipulation;
        $this->em                   = $em;
        $this->fileManager          = $fileManager;
        $this->urlShortenerHelper   = $urlShortenerHelper;
        $this->trackedFileRepo      = $trackedFileRepo;
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
     * Use the ImageOverlay and Template entities to create a composite images/PDF that places the "overlay" (e.g. QR code) onto the provided $canvas File entity
     * and save/persist the new image to storage (i.e. AWS S3) as File entity
     *
     * $generateImmediately = false will simply mark the TrackedFile for creation - and not acctually create it (due to delay when creating large amounts of images).
     */
    public function createCompositeImage(File $canvas, Template $template, array $payload, $generateImmediately = true): File
    {
        $canvas->hasRelatedTemplate($template);

//        $trackedFile = new TrackedFile($canvas, $);

        // loop through ImageOverlay entities and apply them to the canvas
        $overlays = $template->getRelatedImageOverlays();
        foreach($overlays as $curI => $curOverlay) {
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

        // save the file to storage (AWS S3)
        $filePath = "var/QRCodeComposites/overlayTestResult.png";
        $this->imageManipulation->saveImage($composite, $filePath);

        $composite = $this->fileManager->persistFile($filePath);
        $composite->setRelatedOriginalFile($canvas);

        return $composite;
    }

    /**
     *
     */
    public function createBatch()
    {
        $this->trackedFileRepo->createNewTrackedFile();
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
        // todo: remove all TrackedFiles too
        // todo: remove all URLs and hits?
        // todo: delete batch entities?
        $this->fileManager->deleteFile($file);
    }
}
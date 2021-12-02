<?php
/*
* created on: 30/11/2021 - 13:34
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Services\Image;

use App\Entity\FileManager\File;
use App\Entity\FileManager\Template;
use App\Repository\FileManager\ImageOverlayRepository;
use App\Repository\FileManager\TemplateRepository;
use Doctrine\ORM\EntityManager;
use VisageFour\Bundle\ToolsBundle\Services\FileManager\FileManager;
use VisageFour\Bundle\ToolsBundle\Services\QRcode\QRCodeGenerator;

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
     * @var QRCodeGenerator
     */
    private $QRCodeGenerator;

    public function __construct(TemplateRepository $templateRepo, ImageOverlayRepository $overlayRepo, ImageManipulation $imageManipulation, EntityManager $em, FileManager $fileManager, QRCodeGenerator $QRCodeGenerator)
    {
        $this->templateRepo         = $templateRepo;
        $this->overlayRepo          = $overlayRepo;
        $this->imageManipulation    = $imageManipulation;
        $this->em                   = $em;
        $this->fileManager          = $fileManager;
        $this->QRCodeGenerator      = $QRCodeGenerator;
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
     * Create a composite images/PDF that places the "overlay" (e.g. QR code) onto the provided $canvas File entity
     * and save the new image as File entity (i.e. to AWS S3)
     */
    public function createCompositeImage(File $canvas, Template $template, array $payload): File
    {
        // todo: check payload
        $url = $payload['url'];

        // generate the QR code

        $QRCodePathname = $this->QRCodeGenerator->generateShortUrlQRCodeFromURL($url);

//        $baseDir = 'src/VisageFour/Bundle/ToolsBundle/Tests/TestFiles/Image/';
        $composite = $this->imageManipulation->overlayImage (
            $canvas->getLocalFilePath(),
            $QRCodePathname,
            350,
            640,
            0,
            90
        );

        $filePath = "var/ImageManipulation/overlayTestResult.png";
        $this->imageManipulation->saveImage($composite, $filePath);

        $composite = $this->fileManager->persistFile($filePath);

        return $composite;
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
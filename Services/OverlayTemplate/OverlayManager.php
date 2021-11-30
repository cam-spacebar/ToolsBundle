<?php
/*
* created on: 30/11/2021 - 13:34
* by: Cameron
*/

namespace App\VisageFour\Bundle\ToolsBundle\Services\OverlayTemplate;

use App\Entity\FileManager\File;
use App\Repository\FileManager\ImageOverlayRepository;
use App\Repository\FileManager\TemplateRepository;

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

    public function __construct(TemplateRepository $templateRepo, ImageOverlayRepository $overlayRepo)
    {
        $this->templateRepo = $templateRepo;
        $this->overlayRepo = $overlayRepo;
    }

    public function createNewTemplateSimple(File $canvasFile, $posX, $posY, $w, $h, $url)
    {
        // create template and imageOverlay
        $this->templateRepo->createNewTemplate(

        );

        // populate payload

        // generate composite

        // upload composite to AWS S3
        
    }
}
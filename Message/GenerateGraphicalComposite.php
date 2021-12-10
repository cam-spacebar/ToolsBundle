<?php
/*
* created on: 10/12/2021 - 15:40
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Message;

use App\Entity\FileManager\Template;
use VisageFour\Bundle\ToolsBundle\Entity\PrintAttribution\TrackedFile;

/**
 * Class GenerateGraphicalComposite
 * @package VisageFour\Bundle\ToolsBundle\Message
 *
 * Use the ImageOverlay and Template entities to create a composite image/PDF that places the "overlay" (e.g. QR code with short URL) onto the provided $canvas File entity
 * then save/persist the new composite image to storage (i.e. AWS S3) as a File entity
 */
class GenerateGraphicalComposite
{
    /**
     * @var Template
     */
    private $template;
    private $payload;
    /**
     * @var TrackedFile
     */
    private $trackedFile;

    public function __construct(Template $template, $payload, TrackedFile $trackedFile)
    {
        $this->template = $template;
        $this->payload = $payload;
        $this->trackedFile = $trackedFile;
    }

    /**
     * @return Template
     */
    public function getTemplate(): Template
    {
        return $this->template;
    }

    /**
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @return TrackedFile
     */
    public function getTrackedFile(): TrackedFile
    {
        return $this->trackedFile;
    }

}
<?php
/*
* created on: 10/12/2021 - 15:40
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Message;

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
     * @var TrackedFile
     */
    private $trackedFileId;

    // Handler:
    public function __construct(TrackedFile $trackedFile)
    {
        $this->trackedFileId = $trackedFile->getId();
    }

    /**
     * @return TrackedFile
     */
    public function getTrackedFileId(): Int
    {
        return $this->trackedFileId;
    }

}
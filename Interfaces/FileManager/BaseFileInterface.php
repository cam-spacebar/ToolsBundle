<?php

namespace VisageFour\Bundle\ToolsBundle\Interfaces\FileManager;

use VisageFour\Bundle\ToolsBundle\Entity\PrintAttribution\TrackedFile;

interface BaseFileInterface
{
    public function getOriginalFilename();
    public function getFileExtension();
}
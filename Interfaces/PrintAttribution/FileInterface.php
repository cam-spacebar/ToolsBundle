<?php
/*
* created on: 09/12/2021 - 18:11
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Interfaces\PrintAttribution;

use VisageFour\Bundle\ToolsBundle\Entity\PrintAttribution\TrackedFile;
use VisageFour\Bundle\ToolsBundle\Interfaces\FileManager\BaseFileInterface;

interface FileInterface extends BaseFileInterface
{
    public function getRelatedTrackedFile(): ?TrackedFile;
}
<?php

namespace VisageFour\Bundle\ToolsBundle\Interfaces\FileManager;

interface FileInterface
{
    public function getOriginalFilename();
    public function getFileExtension();
}
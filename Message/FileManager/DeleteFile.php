<?php
/*
* created on: 13/12/2021 - 19:03
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Message\FileManager;

use App\Entity\FileManager\File;
use VisageFour\Bundle\ToolsBundle\Message\BaseMessage;

class DeleteFile extends BaseMessage
{
    private $fileId;

    public function __construct (File $file)
    {
        $id = $file->getId();
        $this->checkEntityIDIsNotEmpty($id);
        $this->fileId = $id;
    }

    /**
     * @return int|null
     */
    public function getFileId(): ?int
    {
        return $this->fileId;
    }
}
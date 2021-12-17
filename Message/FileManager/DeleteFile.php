<?php
/*
* created on: 13/12/2021 - 19:03
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Message\FileManager;

use App\Entity\FileManager\File;
use VisageFour\Bundle\ToolsBundle\Classes\Messenger\BaseEntityMessage;

class DeleteFile extends BaseEntityMessage
{
    public function __construct (File $file)
    {
        parent::__construct($file);
    }
}
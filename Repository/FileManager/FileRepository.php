<?php
/*
* created on: 21/11/2021 - 15:45
* by: Cameron
*/

namespace App\VisageFour\Bundle\ToolsBundle\Repository\FileManager;

use VisageFour\Bundle\ToolsBundle\Entity\FileManager\File;
use VisageFour\Bundle\ToolsBundle\Repository\NoAutowire\BaseRepository;

class FileRepository extends BaseRepository
{
    public function createNewFromRealFile (string $filename)
    {
        $new = new File($filename);

        $this->persistAndLogEntityCreation($new);

        return $new;
    }
}
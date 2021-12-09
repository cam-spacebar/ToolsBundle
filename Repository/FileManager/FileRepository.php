<?php
/*
* created on: 21/11/2021 - 15:45
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Repository\FileManager;

use Doctrine\Persistence\ManagerRegistry;
use VisageFour\Bundle\ToolsBundle\Entity\FileManager\BaseFile;
use VisageFour\Bundle\ToolsBundle\Repository\NoAutowire\BaseRepository;

class FileRepository extends BaseRepository
{
//    public function __construct (ManagerRegistry $registry, $class = File::class) {
//        parent::__construct($registry, $class);
//    }

    public function createNewFromRealFile (string $filename)
    {
        $new = new BaseFile($filename);

        $this->persistAndLogEntityCreation($new);

        return $new;
    }
}
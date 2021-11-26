<?php
/*
* created on: 20/11/2021 - 12:49
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Services\FileManager;

/*
 * This class manages uploading and downloading files from services like Amazon S3
 */

use App\Entity\FileManager\File;
use App\Repository\FileManager\FileRepository;
use Doctrine\ORM\EntityManager;
use League\Flysystem\FilesystemInterface;
use VisageFour\Bundle\ToolsBundle\Traits\LoggerTrait;

class ImageOverlayManager
{
    use LoggerTrait;

    private $em;
    private $fileSystem;

    public function __construct(EntityManager $em, FilesystemInterface $publicUploadsFilesystem, FileRepository $fileRepo, string $env_var_bucketName)
    {
        $this->em                   = $em;
        $this->fileSystem           = $publicUploadsFilesystem;
    }

    /**
     * @param $filepath
     * @param null $targetSubfolder
     *
     *
     */
    private function xxx(File $file )
    {
//        $parts = pathinfo($filepath);
//        $filename = $parts['basename'];
//
//        $subfolder = (empty($targetSubfolder)) ? '' : $targetSubfolder .'/';
//
//        $fullPath = $subfolder . $filename;
//        $this->logger->info('Candidate remote $fullPath: '. $fullPath);
//
//        // check if the filepath already exists on the remote server
//        if ($this->fileSystem->has($fullPath)) {
//            $curExt = pathinfo($fullPath, PATHINFO_EXTENSION);
//            $curName = pathinfo($fullPath, PATHINFO_FILENAME);
//            $maxLoops = 1000;
//            for ($i = 1; $i <= $maxLoops; $i++) {
//
//                $fullPath = $subfolder. $curName .'_'. $i .'.'. $curExt;
////                print "\n". $fullPath ."\n";
//                $this->logger->info($curName .'_'. $curExt);
//
//                $isApproved = !$this->fileSystem->has($fullPath);
//                if ($isApproved) {
////                    print 'fullpath approved: '. $fullPath ."\n";
//                    return $fullPath;
//                }
////                print ($isApproved) ? 'yes' : 'no';
//            }
//            throw new \Exception('exceeded '. $maxLoops .' loops - trying to find a filename.');
//        }
//
//        return $fullPath;
    }
}
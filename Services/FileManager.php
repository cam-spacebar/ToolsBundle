<?php
/*
* created on: 20/11/2021 - 12:49
* by: Cameron
*/

namespace App\VisageFour\Bundle\ToolsBundle\Services;

/*
 * This class manages uploading and downloading files from services like Amazon S3
 */

use Doctrine\ORM\EntityManager;
use League\Flysystem\FilesystemInterface;
use VisageFour\Bundle\ToolsBundle\Traits\LoggerTrait;

class FileManager
{
    use LoggerTrait;

    private $em;
    private $fileSystem;

    public function __construct(EntityManager $em, FilesystemInterface $fileSystem)
    {
        $this->em                   = $em;
        $this->fileSystem           = $fileSystem;
    }
//    public function downloadImage(image $image)
//    {
//        $remoteFilename     = $image->getFilename();
//
//        // remove %gaufrette_amazon_s3_folder% folder reference from original name when requesting the file
//        $remoteFilename = str_replace($this->gauretteFolder .'/', '', $remoteFilename);
//
//        if ($this->gaufretteFS->has($remoteFilename)) {
//            $imageTargetPath = $image->getImageStoreFilename();
//            $this->createFolderIfDoesNotExist($imageTargetPath);
//
//            $imageData = $this->gaufretteFS->read($remoteFilename);
//            $bytesWritten = file_put_contents($imageTargetPath, $imageData);
//            $this->logger->info('Successfully downloaded image to: "'. $imageTargetPath .'". '. $image->getLoggerExtraInfo());
//        } else {
//            $this->logger->error('image with filename: '. $remoteFilename .' does not exist on AS S3. Cannot download. '. $image->getLoggerExtraInfo());
//
//            // couldn't find image in Gaufrette FS
//            $image->setIsDeleted(true);
//            $this->em->persist($image);
//            $this->em->flush();
//
//            throw new \Exception ('Image exists in DB but not in Gaufrette FS. Image filename: "' . $remoteFilename . '"');
//        }
//
//        return $image;
//    }

    public function PersistFile ($filePath, $targetFilepath) {
        // todo: create a record for the file in DB
        // todo: check for duplicate upload?

        if (!is_file($filePath)) {
            // don't throw exception for worker processes, use log instead
            $errMsg = 'Cannot find image with path: '. $filePath;
            $this->logger->error($errMsg);
            //throw new \Exception ($errMsg2);
        }

        $imageData = file_get_contents($filePath);
        $pathParts = pathinfo($filePath);

        // persist image to off-server storage
        if (!$this->fileSystem->has($targetFilepath)) {
            $this->fileSystem->write($targetFilepath, $imageData);
        } else {
            // it may not occur due to the image->Id being added to each filename
            throw new \Exception ('This file already exists');
        }

        $infoMsg = "Persisted image to S3. Local filename: ". $filePath .' Target filename: '. $targetFilepath ."\n";
        //$this->consoleOutput ($consoleMsg);
        $this->logger->info($infoMsg);

        return true;
    }
}
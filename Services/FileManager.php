<?php
/*
* created on: 20/11/2021 - 12:49
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Services;

/*
 * This class manages uploading and downloading files from services like Amazon S3
 */

use App\Repository\FileManager\FileRepository;
use Doctrine\ORM\EntityManager;
use League\Flysystem\FilesystemInterface;
use VisageFour\Bundle\ToolsBundle\Traits\LoggerTrait;

class FileManager
{
    use LoggerTrait;

    private $em;
    private $fileSystem;
    private $fileRepo;

    public function __construct(EntityManager $em, FilesystemInterface $publicUploadsFilesystem, FileRepository $fileRepo)
    {
        $this->em                   = $em;
        $this->fileSystem           = $publicUploadsFilesystem;
        $this->fileRepo             = $fileRepo;
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

    public function deleteRemoteFile($remoteFilepath, $throwExceptionIfFileDoesNotExist = true)
    {
        if ($this->fileSystem->has($remoteFilepath)) {
            $this->fileSystem->delete($remoteFilepath);
        } else {
            if ($throwExceptionIfFileDoesNotExist) {
                throw new \Exception('File does not exist. (filepath: "'. $remoteFilepath .'")');
            }
        }
    }

    public function persistFile ($filePath, $targetFilepath, $overwriteFile = false) {
        // todo: create a record for the file in DB
        // todo: check for duplicate upload?

        if (!is_file($filePath)) {
            // todo: don't throw exception for worker processes, use log instead
            $errMsg = 'Cannot find file with path: '. $filePath;
//            $this->logger->error($errMsg);
            throw new \Exception ($errMsg);
        }

        $stream = fopen($filePath, 'r');
        $pathParts = pathinfo($filePath);

        // persist image to remote storage
        if (!$this->fileSystem->has($targetFilepath) && !$overwriteFile) {
            $result = $this->fileSystem->writeStream($targetFilepath, $stream);
        } else {
            throw new \Exception ('This file already exists');
        }

        if (is_resource($stream)) {
            fclose($stream);
        }

        $infoMsg = "File Persisted to remote storage. Local filename: ". $filePath .' Target filename: '. $targetFilepath ."\n";
        //$this->consoleOutput ($consoleMsg);
        $this->logger->info($infoMsg);

        $this->fileRepo->createNewByFilepath($filePath, $targetFilepath);

        return $result;
    }

    public function downloadFile ($filePath, $targetFilepath, $overwriteFile = false) {

    }

    /**
     * @return FileRepository
     */
    public function getFileRepo(): FileRepository
    {
        return $this->fileRepo;
    }
}
<?php
/*
* created on: 13/12/2021 - 19:05
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\MessageHandler\FileManager;

use App\Entity\FileManager\File;
use App\Repository\FileManager\FileRepository;
use App\VisageFour\Bundle\ToolsBundle\Classes\Messenger\BaseEntityHandler;
use Doctrine\ORM\EntityManager;
use League\Flysystem\FilesystemInterface;
use VisageFour\Bundle\ToolsBundle\Message\FileManager\DeleteFile;

// Delete the remote and local file and the File DB record (for the File entity provided)
class DeleteFileHandler extends BaseEntityHandler
{

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var FileRepository
     */
    private $fileRepository;

    /** @var FilesystemInterface */
    private $fileSystem;

    public function __construct (EntityManager $em, FileRepository $fileRepository, FilesystemInterface $publicUploadsFilesystem)
    {
        $this->em = $em;
        $this->fileRepository = $fileRepository;
        $this->fileSystem = $publicUploadsFilesystem;

        parent::__construct();
    }

    public function __invoke(DeleteFile $msg)
    {
        $this->handleMessage($msg);
        return true;
    }

    public function runProcess(int $entityId)
    {
        /** @var File $file */
        $file = $this->fileRepository->findOneByIdOrException($entityId);

        if (!$this->checkStatusIsAcceptable($file)) {
            // if status is not acceptable ??

            return true;
        }

        $logMsg = 'deleting file (from remote, local and DB record) with id: '. $file->getId() .', (original basename: '. $file->getOriginalBasename() .')';
        $this->logger->info($logMsg, [], 'grey_bold');
//        die('12333');
        if (!$file->getRelatedTemplates()->isEmpty()) {
//            dump($file->getRelatedTemplates());
            $count = $file->getRelatedTemplates()->count();
            throw new \Exception('the file: "'. $file->getOriginalBasename() .'" (id: '. $file->getId() .') has '. $count .' template entity/entities (a foreign key) so it can not be deleted directly. Please use: OverlayManager->deleteFile() to remove template, overlays and the file (image / pdf).');
        }

        $remoteFilepath = $file->getRemoteFilePath();
        try {
            $this->deleteRemoteFile($remoteFilepath);
        } catch (\Exception $e) {
            // throw an  with a more specific msg
            $msg = 'File (id: '. $file->getId() .') does not exist. (filepath: "'. $remoteFilepath .'", originalFilename: "'. $file->getOriginalFilename() .'")';
            throw new \Exception($msg);
        }

        $this->deleteLocalFile($file);
        $this->fileRepository->MarkAsDeleted($file);

        $this->em->remove($file);

//        $this->logger->info('Deleted file (from remote, local and DB record) with original filename: '. $file->getOriginalBasename());
        return true;
    }

    // check the composite hasn't been generated already, or has (or will be) deleted.
    private function checkStatusIsAcceptable (File $file) {
//        update with $file and check statuses
        $this->logger->info('$file status: '. $file->getStatus());

        $msgSuffix = 'Cannot delete File entity (with: '. $file->getId() .')';
        $status = $file->getStatus();
        if ($status == File::STATUS_DELETED) {
            // do nothing.
            $this->logger->alert($msgSuffix. ', it is marked as already been deleted.', $file);
            return false;
        }

        if (!($status == File::STATUS_MARKED_FOR_DELETION)) {
            $this->logger->alert($msgSuffix .', it must be marked as File::STATUS_MARKED_FOR_DELETION to be deleted.', $file);
            return false;
        }

        return true;
    }

    /**
     * @param string $remoteFilepath
     * @param bool $throwExceptionIfFileDoesNotExist
     * @return bool
     * @throws \League\Flysystem\FileNotFoundException
     *
     * Delete the remote (S3) file, but do not affect the DB record or locally cached file.
     */
    public function deleteRemoteFile(string $remoteFilepath, $throwExceptionIfFileDoesNotExist = true)
    {
        if ($this->fileSystem->has($remoteFilepath)) {
            $this->logger->info('Removing remote file (from AWS S3), filepath: '. $remoteFilepath, [], 'grey_bold');
            return $this->fileSystem->delete($remoteFilepath);
        } else {
            if ($throwExceptionIfFileDoesNotExist) {
                throw new \Exception('File does not exist. (filepath: "'. $remoteFilepath .'")');
            }
        }

        return true;
    }

    /**
     * @param File $file
     * @return bool
     *
     * delete ony the locally cached file.
     */
    public function deleteLocalFile(File $file)
    {
        $filepath = $file->getLocalFilePath();
        if (is_file($filepath)) {
            unlink($filepath);
        }

        $this->logger->info('deleted local cached file: '. $filepath, [], 'grey_bold');
        return true;
    }
}
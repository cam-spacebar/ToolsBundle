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

class FileManager
{
    use LoggerTrait;

    private $em;
    private $fileSystem;
    private $fileRepo;
    private $bucketname;

    // returns true if the file exists in cache (used for aserts in testing).
    private $isLastCacheHitSuccessful;

    public function __construct(EntityManager $em, FilesystemInterface $publicUploadsFilesystem, FileRepository $fileRepo, string $env_var_bucketName)
    {
        $this->em                   = $em;
        $this->fileSystem           = $publicUploadsFilesystem;
        $this->fileRepo             = $fileRepo;
        $this->bucketname           = $env_var_bucketName;
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
        // todo: remove this and just use the flysystem exceptions instead?

        if ($this->fileSystem->has($remoteFilepath)) {
            return $this->fileSystem->delete($remoteFilepath);
        } else {
            if ($throwExceptionIfFileDoesNotExist) {
                throw new \Exception('File does not exist. (filepath: "'. $remoteFilepath .'")');
            }
        }
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

        $this->logger->info('deleted locally cached file: '. $filepath);
        return true;
    }

    /**
     * @param File $file
     * @throws \League\Flysystem\FileNotFoundException
     * Delete the remote and local file and the File DB record
     */
    public function deleteFile(File $file)
    {
        if (!$file->getRelatedTemplates()->isEmpty()) {
//            dump($file->getRelatedTemplates());
            $count = $file->getRelatedTemplates()->count();
            throw new \Exception('the file: "'. $file->getOriginalFilename() .'" (id: '. $file->getId() .') has '. $count .' template entity/entities (a foreign key) so it can not be deleted directly. Please use: OverlayManager->deleteFile() to remove template, overlays and the file (image / pdf).');
        }

        $remoteFilepath = $file->getRemoteFilePath();
        $this->deleteRemoteFile($remoteFilepath);

        $this->deleteLocalFile($file);
        $this->fileRepo->deleteFile($file);

        $this->em->remove($file);

        $this->logger->info('Deleted file (from remote, local and DB record) with original filename: '. $file->getOriginalFilename());
    }

    public function doesRemoteFileExist($filepath)
    {
        return ($this->fileSystem->has($filepath));
    }

    /**
     * @param $haystack
     * @param $needle
     * @return bool
     * returns true if the last character/s of $haystack is $needle
     * it's useful in preventing paths being passed in with an ending "/" (as this is often added).
     */
    static public function throwExceptionIfEndsWith( $haystack, $needle ) {
        $length = strlen( $needle );
        if( !$length ) {
            throw new \Exception ('string cannot end with a "'. $needle .'". String: '. $haystack );
//            return true;
        }

        return substr( $haystack, -$length ) === $needle;
    }

    /**
     * @param $filepath
     * @param null $targetSubfolder
     *
     * Create a target path for the remote file that isn't currently used in the remote storage (i.e. a unique filepath).
     */
    private function createRemoteFilepath($filepath, $targetSubfolder = null)
    {
        $parts = pathinfo($filepath);
        $filename = $parts['basename'];

        $subfolder = (empty($targetSubfolder)) ? '' : $targetSubfolder .'/';

        $fullPath = $subfolder . $filename;
        $this->logger->info('Candidate remote $fullPath: '. $fullPath);

        // check if the filepath already exists on the remote server
        if ($this->fileSystem->has($fullPath)) {
            $curExt = pathinfo($fullPath, PATHINFO_EXTENSION);
            $curName = pathinfo($fullPath, PATHINFO_FILENAME);
            $maxLoops = 1000;
            for ($i = 1; $i <= $maxLoops; $i++) {

                $fullPath = $subfolder. $curName .'_'. $i .'.'. $curExt;
//                print "\n". $fullPath ."\n";
                $this->logger->info($curName .'_'. $curExt);

                $isApproved = !$this->fileSystem->has($fullPath);
                if ($isApproved) {
//                    print 'fullpath approved: '. $fullPath ."\n";
                    return $fullPath;
                }
//                print ($isApproved) ? 'yes' : 'no';
            }
            throw new \Exception('exceeded '. $maxLoops .' loops - trying to find a filename.');
        }

        return $fullPath;
    }

    /**
     * Uploads the file to remote storage (AWS S3), create a DB record: File (And persists it to the DB)
     * then copy the file to the local cache folder.
     */
    public function persistFile ($filePath, $targetSubfolder = null):File {
        if (!is_file($filePath)) {
            // todo: don't throw exception for worker processes, use log instead
            $errMsg = 'Cannot find file with path: '. $filePath;
//            $this->logger->error($errMsg);
            throw new \Exception ($errMsg);
        }

        $targetFilepath = $this->createRemoteFilepath($filePath, $targetSubfolder);

        $this->writeRemoteFile($filePath, $targetFilepath);

        Work from here:
         - give derivative files an appropriate file name
         - check the files are being stord in cache.
         - delete remote files at the end of the test - and check that all files have been deleted (remote, original and cache)?

        $infoMsg = "File Persisted to remote storage. Local filename: ". $filePath .' Target (remote storage) filename: '. $targetFilepath ."\n";
        //$this->consoleOutput ($consoleMsg);
        $this->logger->info($infoMsg);
        print "\n ". $infoMsg ."\n";

        $newFile = $this->fileRepo->createNewByFilepath($filePath, $targetFilepath);

        $this->copyFileToCache($filePath, $newFile);

        return $newFile;
    }

    /**
     * @param string $localFilepath
     * @param $remoteFilepath
     * @return bool
     * @throws \League\Flysystem\FileExistsException
     *
     * Write the file to the remote server (And close stream)
     */
    private function writeRemoteFile(string $localFilepath, $remoteFilepath)
    {
        $stream = fopen($localFilepath, 'r');

        $result = $this->fileSystem->writeStream($remoteFilepath, $stream);

        if (is_resource($stream)) {
            fclose($stream);
        }

        return $result;
    }

    /**
     * Copy the original file to the cached files folder
     * (why: this prevents a download from the remote storage - as the original file likely exists in an unusual place.)
     */
    private function copyFileToCache($originalFilepath, File $file)
    {

        $cacheFilepath = $this->generateUniqueLocalFilepath($file);

        $logMsg = "caching file to local filesystem. \$originalFilepath: ". $originalFilepath . ", \$cacheFilepath:". $cacheFilepath;
        $this->logger->info($logMsg);
        print "\n" . $logMsg  ."\n";

        $this->createLocalDirectories($cacheFilepath);
        copy( $originalFilepath, $cacheFilepath );

        return true;
    }

    /**
     * @param File $file
     * @param $localFilepath
     *
     * Finds cache file or downloads $file from the remote storage to the local filesystem (cache).
     */
    public function getLocalFilepath (File $file, $subFolder = null) {
        // check for file in local storage first
        $cachedFP = $this->getCachedFilepath($file);

        if ($cachedFP == false) {
//            print "\n cache miss";
            $this->isLastCacheHitSuccessful = false;
            return $this->downloadFile($file);
        }

//        print "\n cache hit";
        $this->isLastCacheHitSuccessful = true;

        return $cachedFP;
    }

    /**
     * Return false if no cache file exists, or return the $filepath to the cached file (if it does exist).
     */
    private function getCachedFilepath(File $file)
    {
        $localFilename = $this->generateUniqueLocalFilepath($file);
//        print "\n file name: ". $localFilename;
        if (is_file($localFilename)) {
//            print "\n".'CACHE HIT';
            // we can assume this is the same file as the remote file due to explanation at marker: #uniqueNaming1
            $this->logger->info('cache hit: file is in local cache.');
            return $localFilename;
        }

//        print "\n".'CACHE MISS';

        return false;
    }

    /**
     * @throws \League\Flysystem\FileNotFoundException
     * Download the $file from remote storage into the local cache (i.e. var/)
     */
    private function downloadFile(File $file)
    {
        $localFilename = $this->generateUniqueLocalFilepath($file);
        $rfp = $file->getRemoteFilePath();
        $stream = $this->fileSystem->readStream($rfp);

//        $this->fileSystem->writeStream($destinationFolder.'/testxyz123.txt', $stream);

//        $localFilename = $destinationFolder.'/testxyz123.txt';
//        $stream = fopen($localFilename, 'w');

        $this->createLocalDirectories($localFilename);

        file_put_contents($localFilename, $stream);

        if (is_resource($stream)) {
            fclose($stream);
        }

        return $localFilename;
    }

    /**
     * @param File $file
     * Returns a local filepath that's unique to this file.
     *
     * note: because the path is unique to this file (and mirrors the remote storage path), it can reliably be assumed that if the file exists,
     * it corresponds (i.e. is) the same as the remote file (unless the remote file has been altered since it was last uploaded) - allowing us
     * to cache files locally (and not download them again). (marker: #uniqueNaming1)
     */
    private function generateUniqueLocalFilepath(File $file)
    {
        // note: it looks like flysystem adds a "../" silently (to get out of /public)
        $baseLocalFolder = 'var/awsS3Downloads/'. $this->bucketname;
        return $baseLocalFolder .'/'. $file->getRemoteFilePath();
    }

    /**
     * @param $filepath
     * Recursively create directories (if they don't already exist).
     */
    public function createLocalDirectories($filepath)
    {
        $parts = pathinfo($filepath);
        $dir1 = $parts['dirname'];
//        print "\n".'target directory: '. $dir1 ."\n";

        if (!is_dir($dir1)) {
            // dir doesn't exist, make it
//            print "\n".'creating dir: '. $dir1 ."\n";
            mkdir($dir1, 0777, true);
        }
    }

    /**
     * @return FileRepository
     */
    public function getFileRepo(): FileRepository
    {
        return $this->fileRepo;
    }

    /**
     * @return bool
     */
    public function getIsLastCacheHitSuccessful(): bool
    {
        return $this->isLastCacheHitSuccessful;
    }
}
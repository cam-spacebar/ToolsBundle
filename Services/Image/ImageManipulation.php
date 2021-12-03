<?php
/*
* created on: 20/11/2021 - 12:49
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Services\Image;

/*
 * This class manages uploading and downloading files from services like Amazon S3
 */

use App\Repository\FileManager\FileRepository;
use Doctrine\ORM\EntityManager;
use League\Flysystem\FilesystemInterface;
use VisageFour\Bundle\ToolsBundle\Classes\ImageOverlay\Image;
use VisageFour\Bundle\ToolsBundle\Services\FileManager\FileManager;
use VisageFour\Bundle\ToolsBundle\Services\UrlShortener\UrlShortenerHelper;
use VisageFour\Bundle\ToolsBundle\Traits\LoggerTrait;

class ImageManipulation
{
    use LoggerTrait;

    private $em;
    private $fileSystem;

    /** @var UrlShortenerHelper */
    private $urlShortener;

    /** @var FileManager */
    private $fileManager;

    public function __construct(EntityManager $em, FilesystemInterface $publicUploadsFilesystem, FileManager $fileManager, FileRepository $fileRepo, string $env_var_bucketName)
    {
        $this->em                   = $em;
        $this->fileSystem           = $publicUploadsFilesystem;
        $this->fileManager          = $fileManager;
    }

    /**
     * @param $filepath
     * @param null $targetSubfolder
     *
     * Loads images from filepath before sending to: overlayImage()
     */
    public function overlayImageByFilePath(string $canvasFilepath, string $topImgFilepath, int $posX, int $posY, int $width, int $height)
    {
        // load images (from filesystem)
        $canvasImg = new Image($canvasFilepath);
        $topImg = new Image($topImgFilepath);

        $compositeImg = $this->overlayImage(
            $canvasImg,
            $topImg,
            $posX,
            $posY,
            $width,
            $height
        );

        return $compositeImg;
    }

    /**
     * @param Image $canvasImg
     * @param Image $topImg
     * @param int $posX
     * @param int $posY
     * @param int $width
     * @param int $height
     * @throws \Exception
     *
     * resizes $topImg and overlays it ontop of $canvasImg to create $compositeImg
     * (note: use "0" for either height or width - if you dont want to resize by this dimension)
     */
    public function overlayImage(Image $canvasImg, Image $topImg, int $posX, int $posY, int $width, int $height): Image
    {
        $newTopImg = $this->resizeImageInProportion($topImg, $width, $height);

        $this->logger->info("creating composite image with: \$posX: $posX, \$posY: $posY, \$width: $width, \$height: $height");

        // create the composite image
        imagecopy(
            $canvasImg->getSrc(),
            $newTopImg->getSrc(),
            $posX,
            $posY,
            0,
            0,
            $newTopImg->getWidth(),
            $newTopImg->getheight()
        );

        return $canvasImg;
    }

    // resize via height dimension or via width:
//    const VIA_HEIGHT = 'height';
//    const VIA_WIDTH = 'width';

    /**
     * @param $file
     * @param $w
     * @param $h
     * @param bool $crop
     * @return false|resource
     *
     * Return an image object that's been resized in "proportion" (i.e. maintaining it's height to width ratio).
     *
     */
    private function resizeImageInProportion(Image $img, $w, $h): Image {
        $width = $img->getWidth();
        $height = $img->getHeight();

        $r = $width / $height;


        if (!empty($w) && !empty($h)) {
            throw new \Exception('you cannot set both width and height, unless $crop is set to false');
        }

        if (!empty($w)) {
            $h = round($w * $r);
        } elseif (!empty($h)) {
            $w = round($h * $r);
        } else {
            throw new \Exception ('you must provide either $w or $h');
        }
        $newwidth = $w;
        $newheight = $h;

        $dst = imagecreatetruecolor($newwidth, $newheight);
        imagecopyresampled($dst, $img->getSrc(), 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

        return new Image (null, $dst);
    }

    public function saveImage(Image $img, string $filepath, $createSubFolders = true)
    {
//        $filepath = "var/ImageOverlay/overlayTestResult.png";
        if ($createSubFolders) {
            $this->fileManager->createLocalDirectories($filepath);
        }

        return imagepng($img->getSrc(), $filepath);
    }
}
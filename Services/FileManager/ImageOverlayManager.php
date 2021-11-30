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
use VisageFour\Bundle\ToolsBundle\Classes\ImageOverlay\Image;
use VisageFour\Bundle\ToolsBundle\Services\UrlShortener\UrlShortenerHelper;
use VisageFour\Bundle\ToolsBundle\Traits\LoggerTrait;

class ImageOverlayManager
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
     * resizes $topImg and overlays it ontop of $canvasImg
     * note use 0 for either height or width - if you dont want to resize by this dimension
     */
    public function overlayImage(string $canvasFilepath, string $topImgFilepath, $posX, $posY, int $width, int $height)
    {
//        $canvas = new Image($canvasFilepath);
//        $topImg = imagecreatefrompng($topImgFilepath);

        $canvasImg = new Image($canvasFilepath);
        $topImg = new Image($topImgFilepath);

        $newTopImg = $this->resizeImageInProportion($topImg, $width, $height, true);

        // overlay the topImage
        $result = imagecopy(
            $canvasImg->getSrc(),
            $newTopImg->getSrc(),
            $posX,
            $posY,
            0,
            0,
            $newTopImg->getWidth(),
            $newTopImg->getheight()
        );

        $filepath = "var/ImageOverlay/overlayTestResult.png";
        $this->fileManager->createLocalDirectories($filepath);
        $result = imagepng($canvasImg->getSrc(), $filepath);

        return $result;
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

//        $src = imagecreatefrompng($file);
//        PRINT "\n\n".'new HEIGHT: '. $newheight;
//        PRINT "\n". 'new Width: '. $newwidth;
//        dump($newwidth, $newheight, $r);
        $dst = imagecreatetruecolor($newwidth, $newheight);
        imagecopyresampled($dst, $img->getSrc(), 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

        return new Image (null, $dst);
    }
}
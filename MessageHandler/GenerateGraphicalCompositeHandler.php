<?php
/*
* created on: 10/12/2021 - 15:37
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\MessageHandler;

use App\Entity\FileManager\ImageOverlay;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use VisageFour\Bundle\ToolsBundle\Classes\ImageOverlay\Image;
use VisageFour\Bundle\ToolsBundle\Message\GenerateGraphicalComposite;
use VisageFour\Bundle\ToolsBundle\Services\FileManager\FileManager;
use VisageFour\Bundle\ToolsBundle\Services\Image\ImageManipulation;
use VisageFour\Bundle\ToolsBundle\Services\UrlShortener\UrlShortenerHelper;
use VisageFour\Bundle\ToolsBundle\Traits\LoggerTrait;

class GenerateGraphicalCompositeHandler implements MessageHandlerInterface
{
    use LoggerTrait;

    /**
     * @var ImageManipulation
     */
    private $imageManipulation;
    /**
     * @var FileManager
     */
    private $fileManager;
    /**
     * @var UrlShortenerHelper
     */
    private $urlShortenerHelper;

    public function __construct(ImageManipulation $imageManipulation, FileManager $fileManager, UrlShortenerHelper $urlShortenerHelper)
    {
        $this->imageManipulation = $imageManipulation;
        $this->fileManager = $fileManager;
        $this->urlShortenerHelper = $urlShortenerHelper;
    }

    public function __invoke(GenerateGraphicalComposite $msg)
    {
        $template = $msg->getTemplate();
        $payload = $msg->getPayload();

        $this->logger->info('Creating composite image from template.', [$template, $payload]);
        $canvas = $template->getRelatedOriginalFile();

//        $trackedFile = new TrackedFile($canvas, $);

        // loop through ImageOverlay entities and apply them to the canvas
        $overlays = $template->getRelatedImageOverlays();
        $i = 0;
        foreach($overlays as $curI => $curOverlay) {
            $i++;
            if ($i > 1) {
                throw new \Exception('this code cant yet handle more than 1 overlay, please update. see: ->overlayImage() needs ouput feedback in.');
            }
            $QRCodeContents = $this->getCurrentPayload($payload, $curOverlay);

            // generate the QR code
            $overlayPathname = $this->urlShortenerHelper->generateShortUrlQRCodeFromURL($QRCodeContents);
            $overlayImg = new Image($overlayPathname);

            // generate the composite (with QR code).
            $composite = $this->imageManipulation->overlayImage (
                $canvas->getImageGD(),
                $overlayImg,
                $curOverlay->getXCoord(),
                $curOverlay->getYCoord(),
                $curOverlay->getWidth(),
                $curOverlay->getHeight()
            );
        }

//        $baseDir = 'src/VisageFour/Bundle/ToolsBundle/Tests/TestFiles/Image/';

        // save composite to local filesystem
        $tempFilename = 'composite_'. uniqid() .'.png';     // use uniqid() here to prevent wasted ->has() call to AWS S3.
        $filePath = "var/composites/temp/". $tempFilename;
        $this->imageManipulation->saveImage($composite, $filePath);

        // save the file to remote storage (AWS S3)
        $remoteSubFolder = 'composites/QRcoded';
        $composite = $this->fileManager->persistFile($filePath, $remoteSubFolder);
        $composite->setRelatedOriginalFile($canvas);
        $canvas->addRelatedDerivativeFile($composite);

        return $composite;
    }

    /**
     * @param array $payload
     * @param ImageOverlay $overlay
     *
     * Look for the $overlay->labelName in payload and return the its value.
     * throw exception if it cannot be found.
     */
    private function getCurrentPayload(array $payload, ImageOverlay $overlay)
    {
        $name = $overlay->getLabelName();
        if (empty($payload[$name])) {
            throw new \Exception('there is no value in the $payload for imageOverlay with labelName: '. $name);
        }

        return $payload[$name];
    }
}
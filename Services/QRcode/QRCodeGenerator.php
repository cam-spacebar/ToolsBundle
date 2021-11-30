<?php
/*
* created on: 29/11/2021 - 13:14
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Services\QRcode;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Alignment\LabelAlignmentCenter;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use VisageFour\Bundle\ToolsBundle\Services\FileManager\FileManager;
use VisageFour\Bundle\ToolsBundle\Services\UrlShortener\UrlShortenerHelper;

class QRCodeGenerator
{
    /** @var UrlShortenerHelper */
    private $urlShortener;

    public function __construct(UrlShortenerHelper $urlShortener, FileManager $fileManager  )
    {
        $this->urlShortener = $urlShortener;
        $this->fileManager  = $fileManager;
    }

    public function generateQRCode($outputPathname, $contents, $overwrite = false)
    {
        if (is_file($outputPathname) && $overwrite) {
            throw new \Exception('cannot overwrite QRcode with pathnae: '. $outputPathname);
        }
        $result = Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data($contents)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size(300)
            ->margin(10)
            ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
//            ->logoPath(__DIR__.'/../../Tests/TestFiles/QRCode/symfony.png')
//            ->labelText('')
//            ->labelFont(new NotoSans(20))
//            ->labelAlignment(new LabelAlignmentCenter())
            ->build();


        $this->fileManager->createLocalDirectories($outputPathname);
        $result->saveToFile($outputPathname);
    }

    /**
     * @param $url
     * @return string
     * @throws \Exception
     *
     * create a Url object from $url, get it's "shortUrl", then create a QR code from the $hortUrl
     */
    public function generateShortUrlQRCodeFromURL($destinationUrl)
    {
        // generate a short Url
        $url = $this->urlShortener->createNewShortenedUrl($destinationUrl);

        // create the QR code
        $pathname = 'var/QRCodes/shortUrls/' . $url->getCode() .'.png';
        $this->generateQRCode($pathname, $url->getShortUrl());

        return $pathname;

    }
}
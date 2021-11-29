<?php
/*
* created on: 28/11/2021 - 15:14
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Services\UrlShortener;

use App\Entity\UrlShortener\Url;
use App\Repository\UrlShortener\HitRepository;
use App\Repository\UrlShortener\UrlRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
use VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode\UrlShortener\InvalidUrlShortCodeException;


class UrlShortenerHelper
{
    /** @var UrlRepository */
    private $urlRepo;

    /** @var HitRepository */
    private $hitRepo;

    /** @var Router */
    private $router;

    public function __construct(UrlRepository $urlRepo, HitRepository $hitRepo, Router $router)
    {
        $this->urlRepo  = $urlRepo;
        $this->hitRepo  = $hitRepo;
        $this->router   = $router;
    }

    // search for the Url (from $code), create a hit if valid and return destination url (or throw error if it doesn't exist).
    public function processShortenedCodeHit(string $code, Request $request)
    {
        /** @var Url $url */
        $url = $this->urlRepo->getByCode($code);

        if (!empty($url)) {
            $this->hitRepo->createNewHit($url, $request);

            // redirect user to that URL
            return $url->getUrlRedirect();
        } else {
            throw new InvalidUrlShortCodeException($code);
        }
    }

    /**
     * @param string $destinationUrl
     * @return string
     * @throws \Exception
     *
     * accepts a $destination URL and return the shortUrl
     */
    public function createNewShortenedUrl(string $destinationUrl): Url
    {
        $url = $this->urlRepo->createNewShortenedUrl($destinationUrl);

        $this->generateShortUrl($url);

        return $url;
    }

    public function generateShortUrl(Url $url)
    {
        $routeName = 'urlShortenedLandingPage';
        $shortUrl = $this->router->generate($routeName,
            array(
                'code'  => $url->getCode()
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $url->setShortUrl($shortUrl);

        return $shortUrl;
    }
}
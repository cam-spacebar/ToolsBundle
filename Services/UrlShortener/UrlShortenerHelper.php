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

class UrlShortenerHelper
{
    /** @var UrlRepository */
    private $urlRepo;

    /** @var HitRepository */
    private $hitRepo;

    public function __construct(UrlRepository $urlRepo, HitRepository $hitRepo)
    {
        $this->urlRepo  = $urlRepo;
        $this->hitRepo = $hitRepo;
    }

    // accept the shortened url code, create a hit if valid and return destination url (or throw error if it doesn't exist).
    public function processShortenedCode(string $code, Request $request)
    {
        /** @var Url $url */
        $url = $this->urlRepo->getByCode($code);

        if (!empty($url)) {
            $this->hitRepo->createNewHit($url, $request);

            // redirect user to that URL
            return $url->getUrlRedirect();
        } else {
            die ('asdf unknonwn 23234');
        }
    }
}
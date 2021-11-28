<?php
/*
* created on: 26/11/2021 - 21:21
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Controller\UrlShortener;

use App\Entity\UrlShortener\Url;
use App\Repository\UrlShortener\HitRepository;
use App\Repository\UrlShortener\UrlRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class LandingPageController extends AbstractController
{
    /**
     * @Route(
     *      path            = "/short/{code}",
     *      name            = "urlShortenedLandingPage"
     * )
     *
     * The landing page for all shortened URLS
     */
    public function LandingPageAction(Request $request, string $code, UrlRepository $urlRepo, HitRepository $hitRepo)
    {
        // todo:
        // retrieve the Url obj
        // 301 redirect user to that URL

        /** @var Url $url */
        $url = $urlRepo->getByCode($code);

        if (!empty($url)) {
            $hitRepo->createNewHit($url, $request);

            // redirect user to that URL
            return $this->redirect($url->getUrlRedirect(), 301);
        } else {
            die ('asdf unknonwn 23234');
        }

//        return $this->redirectToRoute('badgeValidation', array (
//            'id'        => $emailSignInType->getPerson()->getId()
//        ));
    }
}
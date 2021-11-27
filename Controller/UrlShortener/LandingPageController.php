<?php
/*
* created on: 26/11/2021 - 21:21
* by: Cameron
*/

namespace App\VisageFour\Bundle\ToolsBundle\Controller\UrlShortener;

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
    public function LandingPageAction(Request $request, $code, UrlRepository $urlRepo, HitRepository $hitRepo)
    {
        // todo:
        // retrieve the Url obj
        // 301 redirect user to that URL

        $url = $urlRepo->getByCode($code);
        print $request->headers->get('User-Agent');
        print $request->getClientIp();

        if (!empty($url)) {
            // todo: record the hit
//            $hitRepo->createNewHit($url);
            // redirect user to that URL
//            $this->redirect(?)
die('sadf');

        } else {
            die ('asdf unknonwn');
        }

//        return $this->redirectToRoute('badgeValidation', array (
//            'id'        => $emailSignInType->getPerson()->getId()
//        ));
    }
}
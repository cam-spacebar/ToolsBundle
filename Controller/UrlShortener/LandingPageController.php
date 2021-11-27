<?php
/*
* created on: 26/11/2021 - 21:21
* by: Cameron
*/

namespace App\VisageFour\Bundle\ToolsBundle\Controller\UrlShortener;

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
    public function LandingPageAction(Request $request, $code, UrlRepository $urlRepo)
    {
        // todo:
        // retrieve the Url obj
        // 301 redirect user to that URL

        $url = $urlRepo->getByCode($code);

        if (!empty($url)) {
            // todo: record the hit
            // redirect user to that URL
//            $this->redirect(?)
            continue from here  -create the hit then check for it in the test.
            die('324wfewsedc');
        } else {
            die ('asdf unknonwn');
        }

//        return $this->redirectToRoute('badgeValidation', array (
//            'id'        => $emailSignInType->getPerson()->getId()
//        ));
    }
}
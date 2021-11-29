<?php
/*
* created on: 26/11/2021 - 21:21
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Controller\UrlShortener;

use App\Entity\UrlShortener\Url;
use App\Repository\UrlShortener\HitRepository;
use App\Repository\UrlShortener\UrlRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use VisageFour\Bundle\ToolsBundle\Interfaces\ApiErrorCodeInterface;
use VisageFour\Bundle\ToolsBundle\Services\UrlShortener\UrlShortenerHelper;

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
    public function LandingPageAction(Request $request, string $code, EntityManager $em, UrlShortenerHelper $urlShortenerHelper)
    {
        try {
            $url = $urlShortenerHelper->processShortenedCode($code, $request);
            $em->flush();
            return $this->redirect($url, 301);

        } catch (ApiErrorCodeInterface $e) {
            $twigTemplate   = '@ToolsBundle/Resources/views/Errors/genericError.html.twig';
            $parameters     = array (
                'errorMsg' => $e->getMessage()
            );

            return $this->render($twigTemplate, $parameters);
//            return $ra->handleException($e);
        }

//        return $this->redirectToRoute('badgeValidation', array (
//            'id'        => $emailSignInType->getPerson()->getId()
//        ));
    }
}
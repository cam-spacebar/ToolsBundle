<?php

namespace VisageFour\Bundle\ToolsBundle\ClassExtension;

use App\Services\FrontendUrl;
use App\Services\AppSecurity;
use App\Entity\Person;
use VisageFour\Bundle\ToolsBundle\Services\ResponseAssembler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class CustomController extends AbstractController
{
    /**
     * @var ResponseAssembler
     */
    private $ra;

    /**
     * @var AppSecurity
     */
    private $appSecurity;

    public function __construct(ResponseAssembler $ra, AppSecurity $appSecurity)
    {
        $this->ra = $ra;
        $this->appSecurity = $appSecurity;
    }

    /**
     * @param $data
     * @param Person $person
     * @return JsonResponse
     *
     * Adds the "person" field to *every* response.
     * This ensures that when logged out, additional windows / instances of the front-end code will be notified
     * the user is logged out (person: null)
     *
     * $redirect: issues a command to the front-end to redirect the user to a different url on the front-end.
     */
    protected function assembleJsonResponse ($data = null, $redirect = FrontendUrl::NO_REDIRECTION): JsonResponse {
        /** @var Person $loggedInPerson */

        return $this->ra->assembleJsonResponse($data, $redirect);
    }

    protected function getFlashBag () : FlashBagInterface {
        if (!$this->container->has('session')) {
            throw new \LogicException('You can not use the addFlash method if sessions are disabled. Enable them in "config/packages/framework.yaml".');
        }

        return $this->container->get('session')->getFlashBag();
    }

    /**
     * @return \Doctrine\Persistence\ObjectManager
     */
    protected function getEm () {
        return $this->getDoctrine()->getManager();
    }

    protected function getLoggedInPerson($throw = false)
    {
        $this->appSecurity->getLoggedInUser($throw);
    }

    /**
     * @return Person
     * @throws \Twencha\Bundle\EventRegistrationBundle\Exceptions\BaseApiErrorCode
     * get the logged in user, or throw an ApiErroCode that redirects them to the login form (on the front-end)
     */
    protected function getLoggedInUserOrRedirectToLogin(): ?Person
    {
            return $this->appSecurity->getLoggedInUserOrRedirectToLogin();
    }
}
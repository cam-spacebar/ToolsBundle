<?php

namespace VisageFour\Bundle\ToolsBundle\ClassExtension;

use App\Services\AppSecurity;
use App\Entity\Person;
use Symfony\Component\HttpFoundation\Request;
use VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode\MissingInputException;
use VisageFour\Bundle\ToolsBundle\Services\BaseFrontendUrl;
use VisageFour\Bundle\ToolsBundle\Services\ResponseAssembler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use VisageFour\Bundle\ToolsBundle\Interfaces\ApiErrorCodeInterface;

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
     * Note: the "person" field is add to *every* response.
     * This ensures that when logged out, additional windows / instances of the front-end code will be notified
     * the user is logged out (person: null)
     *
     * $redirect: issues a command to the front-end to redirect the user to a different url on the front-end.
     *
     * [invocation examples: https://gist.github.com/cam-spacebar/301d3dc8e9f1fbe0a847864412cd2cdb]
     */
    protected function assembleJsonResponse ($data = null, $redirect = BaseFrontendUrl::NO_REDIRECTION): JsonResponse {
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
     * get the logged in user, or throw an ApiErroCode that redirects them to the login form (on the front-end)
     */
    protected function getLoggedInUserOrRedirectToLogin(): ?Person
    {
            return $this->appSecurity->getLoggedInUserOrRedirectToLogin();
    }

    protected function getPOSTParam(Request $request, string $paramName)
    {
        // this uses: symfony-bundles/json-request-bundle (for the nice shorthand ->get() command)
        $value = $request->get($paramName);

        if (empty($value)) {
            throw new MissingInputException($paramName);
        }

        return $value;
    }
}
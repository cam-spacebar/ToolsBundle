<?php

namespace VisageFour\Bundle\ToolsBundle\Services;

use App\Services\FrontendUrl;
use App\Twencha\Bundle\EventRegistrationBundle\Exceptions\ApiErrorCode;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use VisageFour\Bundle\ToolsBundle\Interfaces\ApiErrorCodeInterface;

/**
 * Class ResponseAssembler
 *
 * Assemble a JSON response (for the front-end client)
 * with a set structure (keys: data, user, error, redirect)
 */
class ResponseAssembler
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorageInterface;

    /**
     * @var FlashBag
     */
    private $flashbag;

    /**
     * @var AuthenticationUtils
     */
    private $authentication_utils;

    /**
     * @var BaseFrontendUrl
     */
    private $baseFrontendUrl;

    public function __construct(TokenStorageInterface $tokenStorageInterface, FlashBagInterface $flashbag, FrontendUrl $frontendUrl, AuthenticationUtils $authentication_utils)
    {
        $this->tokenStorageInterface    = $tokenStorageInterface;
        $this->flashbag                 = $flashbag;
        $this->authentication_utils     = $authentication_utils;
        $this->baseFrontendUrl          = $frontendUrl;
    }

    private function getLoggedInUser () {
        $token = $this->tokenStorageInterface->getToken();

        if (!empty($token)) {
            return $token->getUser();
        }

        return 'anon.';
    }

    /**
     * @param null $data
     * @param null $redirect
     * @return JsonResponse
     * @throws \Exception
     *
     */
    public function assembleJsonResponse ($data = null, $redirect = FrontendUrl::NO_REDIRECTION, ApiErrorCodeInterface $error = null): JsonResponse {
        $rootKeys = [];

        $loggedInUser = $this->getLoggedInUser();
        if ($loggedInUser !== 'anon.') {
            $rootKeys['userData'] = [
                'firstName'  => $loggedInUser->getFirstName(),
                'email' => $loggedInUser->getEmailCanonical()
            ];
        }

        $rootKeys['data'] = $data;

        if ($redirect !== FrontendUrl::NO_REDIRECTION) {
            $rootKeys['redirect'] = $this->baseFrontendUrl->getFrontendURLPart($redirect);
        }

        $rootKeys['success_msgs'] = $this->flashbag->get('success_msgs');        // an array of success messages (is returned)
        $rootKeys['error_msgs'] = $this->flashbag->get('error_msgs');        // an array of success messages (is returned)
        if (!empty($rootKeys['error_msgs'])) {
            throw new \Exception(
                'error messages must not be sent through the flashbag anymore, use the ApiErrorCode class instead. error reads: "'.
                implode(', ', $rootKeys['error_msgs']) .'"'
            );
        }

        if (!empty($error)) {
            $rootKeys['error_msgs'] = $error->getPublicMsg();
            $rootKeys['status'] = $error->getStatusCode();
        } else {
            $rootKeys['status'] = ApiErrorCode::OK;
        }

        $payload = [
        ];

        foreach($rootKeys as $curKey => $curValue) {
            if (empty($curValue)) { continue; }

            $payload[$curKey] = $curValue;
        }

        return new JsonResponse($payload);
    }

    public function addErrorMessage($msg)
    {
        throw new \Exception('this should no longer be used. use the ApiErrorCode class instead.');
        $this->flashbag->add('error_msgs', $msg);
    }

    public function addSuccessMessage($msg)
    {
        $this->flashbag->add('success_msgs', $msg);
    }
}
<?php

namespace VisageFour\Bundle\ToolsBundle\Services;

use App\Services\FrontendUrl;
use App\Exceptions\ApiErrorCode;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use VisageFour\Bundle\ToolsBundle\Interfaces\ApiErrorCodeInterface;
use VisageFour\Bundle\ToolsBundle\Traits\LoggerTrait;

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

    use LoggerTrait;

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
     * @param ApiErrorCodeInterface $e
     * @return JsonResponse
     * @throws \Exception
     *
     * return a JsonResponse object, based on the APIErrorCode on file (for the exception).
     */
    public function handleException (ApiErrorCodeInterface $e)
    {
        $this->logger->info("Exception caught, class name: ". get_class($e));
        $this->logger->info("Exception message: ". $e->getMessage());
        return $this->assembleJsonResponse(null, $e->getRedirectionCode(), $e);
    }

    /**
     * @param null $data
     * @param string $redirect
     * @param ApiErrorCodeInterface|null $error
     * @param array $redirectData
     * @return JsonResponse
     * @throws \Exception
     */
    public function assembleJsonResponse ($data = null, $redirect = FrontendUrl::NO_REDIRECTION, ApiErrorCodeInterface $error = null, $redirectData = []): JsonResponse {
        $rootKeys = [];

        $loggedInUser = $this->getLoggedInUser();
        if ($loggedInUser !== 'anon.') {
            $rootKeys['userData'] = [
                'firstName'  => $loggedInUser->getFirstName(),
                'email' => $loggedInUser->getEmailCanonical()
            ];
        }

        $rootKeys['data'] = $data;
//        dump($data);

        if ($redirect !== FrontendUrl::NO_REDIRECTION) {
            $rootKeys['redirect'] = $this->baseFrontendUrl->getFrontendUrl($redirect, $redirectData);
        }

        $rootKeys['success_msgs'] = $this->flashbag->get('success_msgs');    // an array of success messages (is returned)
        $rootKeys['error_msgs'] = $this->flashbag->get('error_msgs');        // an array of success messages (is returned)
        if (!empty($rootKeys['error_msgs'])) {
            throw new \Exception(
                'error messages must not be sent through the flashbag anymore, use the ApiErrorCode class instead. error reads: "'.
                implode(', ', $rootKeys['error_msgs']) .'"'
            );
        }

        $HTTPStatusCode = 200;
        if (!empty($error)) {
            $respMsg = $error->getStandardResponseMsg();
            $HTTPStatusCode = $error->getHTTPStatusCode();

            $rootKeys['error_msgs'] = $respMsg; // $error->getPublicMsg();
            $rootKeys['status'] = $error->getValue();
        } else {
            $rootKeys['status'] = ApiErrorCode::OK;
        }

        $payload = [
        ];

        foreach($rootKeys as $curKey => $curValue) {
            if (empty($curValue)) { continue; }

            $payload[$curKey] = $curValue;
        }


        return new JsonResponse($payload, $HTTPStatusCode);
    }

    public function addErrorMessage($msg)
    {
        throw new \Exception('this should no longer be used. use the ApiErrorCode class instead.');
//        $this->flashbag->add('error_msgs', $msg);
    }

    public function addSuccessMessage($msg)
    {
        $this->flashbag->add('success_msgs', $msg);
    }
}
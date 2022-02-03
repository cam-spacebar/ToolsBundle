<?php

namespace VisageFour\Bundle\ToolsBundle\Services;

use App\Services\FrontendUrl;
use App\Exceptions\ApiErrorCode;
use VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode\CombinedException;
use VisageFour\Bundle\ToolsBundle\Services\ApiStatusCode\LogException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Serializer\SerializerInterface;
use VisageFour\Bundle\ToolsBundle\Classes\ApiStatusCode\VFApiStatusCodes;
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
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var LogException
     */
    private $logException;

    use LoggerTrait;

    public function __construct(TokenStorageInterface $tokenStorageInterface, FlashBagInterface $flashbag, FrontendUrl $frontendUrl, AuthenticationUtils $authentication_utils, SerializerInterface $serializer, LogException $logException)
    {
        $this->tokenStorageInterface    = $tokenStorageInterface;
        $this->flashbag                 = $flashbag;
        $this->authentication_utils     = $authentication_utils;
        $this->baseFrontendUrl          = $frontendUrl;
        $this->serializer               = $serializer;
        $this->logException             = $logException;
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
     * Log the exception, then return a Symfony JsonResponse object, based on the APIErrorCode on file (for the exception).
     */
    public function handleException (ApiErrorCodeInterface $e)
    {
        $this->logException->run($e);
        return $this->assembleJsonResponse(null, $e->getRedirectionCode(), $e);
    }

    /**
     * accepts an object|array that can be serialized. Just provide the $serializationGroup
     * then passes the result $data array into assembleJsonResponse()
     */
    public function assembleJsonResponseViaSerialization($toSerialize, string $serializationGroup)
    {
        if (!is_object($toSerialize) & !is_array($toSerialize) ) {
            throw new \Exception('the $obj provided is not an object');
        }

        $context = ['groups' => $serializationGroup];
        $array1 = $this->serializer->normalize($toSerialize, null, $context);
        return $this->assembleJsonResponse($array1);
    }

    /**
     * @param null $data
     * @param string $redirect
     * @param ApiErrorCodeInterface|null $error
     * @param array $redirectData
     * @return JsonResponse
     * @throws \Exception
     *
     * return a standardized JSON response to the client.
     */
    public function assembleJsonResponse ($data = null, $redirect = FrontendUrl::NO_REDIRECTION, ApiErrorCodeInterface $error = null, $redirectData = []): JsonResponse {
        $rootKeys = [];
        $this->setLoggedInUser($rootKeys);

        $rootKeys['data'] = $data;

        if ($redirect !== FrontendUrl::NO_REDIRECTION) {
            $rootKeys['redirect'] = $this->baseFrontendUrl->getFrontendUrl($redirect, $redirectData);
        }

        $this->checkFlashBagForMessages($rootKeys);
        $HTTPStatusCode = $this->getHttpStatusCode($error, $rootKeys);
        $this->setErrorMessages($rootKeys, $error);
//        dd($rootKeys);

        $payload = $this->assemblePayload($rootKeys);

        return new JsonResponse($payload, $HTTPStatusCode);
    }

    // Set the logged in user (if they are logged in)
    private function setLoggedInUser($rootKeys)
    {
        $loggedInUser = $this->getLoggedInUser();
        if ($loggedInUser !== 'anon.') {
            $rootKeys['userData'] = [
                'firstName'  => $loggedInUser->getFirstName(),
                'email' => $loggedInUser->getEmailCanonical()
            ];
        }
    }

    // throw exception if flashbag is still being used
    private function checkFlashBagForMessages (&$rootKeys) {
//        $rootKeys['success_msgs'] = $this->flashbag->get('success_msgs');    // an array of success messages (is returned)
//        $rootKeys['error_msgs'] = $this->flashbag->get('error_msgs');        // an array of success messages (is returned)
//        if (!empty($rootKeys['error_msgs'])) {
        if (!empty($this->flashbag->get('error_msgs'))) {
            throw new \Exception(
                'error messages must not be sent through the flashbag anymore, use the ApiErrorCode class instead. error reads: "'.
                implode(', ', $rootKeys['error_msgs']) .'"'
            );
        }
    }

    // (if an error exists) get it's HTTP status code, otherwise use 200:OK. Also set the "response code".
    // also add the error messages.
    private function getHttpStatusCode(ApiErrorCodeInterface $error, &$rootKeys)
    {
        $HTTPStatusCode = 200;

        if (!empty($error)) {
            // if an error exists, then get its status code
            $HTTPStatusCode = $error->getHTTPStatusCode();

            $rootKeys['status'] = $error->getValue();
        } else {
            $rootKeys['status'] = (string) ApiErrorCode::OK;
        }

        return $HTTPStatusCode;
    }

    // if error msg/s exist, add it/them to the return keys.
    private function setErrorMessages(&$rootKeys, ApiErrorCodeInterface $error)
    {
        if (!empty($error)) {
            $rootKeys['error_msgs'] = $error->getPublicMsg();
            if ($error instanceof CombinedException) {

                $rootKeys['combinedErrors'] = $error->getCombinedPublicErrorMessages();
//                dump($error->getCombinedErrorResponses());
            }
        }
//        $rootKeys['test123'] = 'youyou';
    }

    /**
     * Assemble the payload that will be returned to the client.
     */
    private function assemblePayload(array &$rootKeys)
    {
        $payload = [];
        foreach($rootKeys as $curKey => $curValue) {
            if (empty($curValue)) { continue; }
            $payload[$curKey] = $curValue;
        }

        return $payload;
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
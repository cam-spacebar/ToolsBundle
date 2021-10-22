<?php

namespace VisageFour\Bundle\ToolsBundle\Exceptions;

use Symfony\Component\HttpFoundation\Request;
use VisageFour\Bundle\ToolsBundle\Classes\UniqueConstantsList;
use VisageFour\Bundle\ToolsBundle\Interfaces\ApiErrorCodeInterface;
use VisageFour\Bundle\ToolsBundle\Interfaces\UniqueConstantsListInterface;
use VisageFour\Bundle\ToolsBundle\Services\BaseFrontendUrl;
use VisageFour\Bundle\ToolsBundle\Traits\HasAUniqueConstantsListTrait;

/**
 * Extend this and implement application codes that are specific to your project.
 */
class BaseApiErrorCode extends PublicException implements ApiErrorCodeInterface //, UniqueConstantsListInterface
{
    use HasAUniqueConstantsListTrait;

    // security error codes:
    const OK                                    = 10;       // the default of a response, which indicates there were not problems.
    const INPUT_MISSING                         = 20;       // a GET or POST parameter is missing.
    const INVALID_EMAIL_ADDRESS                 = 30;       // the email address provided is invalid.
    const ALREADY_LOGGED_IN                     = 40;       // when a user is attempting to login
    const ERROR_BUT_ALREADY_LOGGED_IN           = 43;       // originally it used the already logged in error (if logged in but failed authentication) - but
                                                            // this could cause confusion so this error code was created
    const INVALID_ACCOUNT_VERIFICATION_TOKEN    = 50;
    const ACCOUNT_ALREADY_VERIFIED              = 60;
    const CHANGE_PASSWORD_TOKEN_INVALID         = 70;
    const INVALID_CREDENTIALS                   = 80;       // incorrect password
    const INVALID_NEW_PASSWORD                  = 90;       // when a user provides a new passwords that's too short or too long (for instance).
    const ACCOUNT_NOT_VERIFIED                  = 100;
    const LOGIN_REQUIRED                        = 110;

    // Add new route marker: #CMDKKD00-generic
    private static $initialStatusCodes = [
        // security errors:
        self::OK                                    => ['msg'               => 'Request fine.',
                                                        'HTTPStatusCode'    => 200],
        self::INPUT_MISSING                         => ['msg'               => 'You are missing an input parameter',
                                                        'HTTPStatusCode'    => 401],
        self::INVALID_EMAIL_ADDRESS                 => ['msg'               => 'Email could not be found.',
                                                        'HTTPStatusCode'    => 401],
        self::ALREADY_LOGGED_IN                     => ['msg'               => 'You are already logged in!',
                                                        'HTTPStatusCode'    => 200],
        self::ERROR_BUT_ALREADY_LOGGED_IN           => ['msg'               => 'There was an error in your login attempt, however you are already logged in.',
                                                        'HTTPStatusCode'    => 400],
        self::INVALID_ACCOUNT_VERIFICATION_TOKEN    => ['msg'               => 'The token provided is invalid. This account cannot be verified.',
                                                        'HTTPStatusCode'    => 400],
        self::ACCOUNT_ALREADY_VERIFIED              => ['msg'               => 'Your account has already been verified.',
                                                        'HTTPStatusCode'    => 400],
        self::CHANGE_PASSWORD_TOKEN_INVALID         => ['msg'               => 'The token provided is invalid. The password cannot be changed. Please re-try the "forgot your password" form to get a new link/token.',
                                                        'HTTPStatusCode'    => 401],
        self::INVALID_CREDENTIALS                   => ['msg'               => 'Invalid credentials.',
                                                        'HTTPStatusCode'    => 401],
        self::INVALID_NEW_PASSWORD                  => ['msg'               => 'The password provided is incorrect.',
                                                        'HTTPStatusCode'    => 401],
        self::ACCOUNT_NOT_VERIFIED                  => ['msg'               => 'Cannot complete this request as this account is not verified. Please check your email (and spam folder) for an email containing a verification link.',
                                                        'HTTPStatusCode'    => 401],        // message inherited from the exception class: AccountNotVerifiedException
        self::LOGIN_REQUIRED                        => ['msg'               => 'You must login first to view this page.',
                                                        'HTTPStatusCode'    => 401]
    ];

    /**
     * @var int|null
     * the FrontendUrl (class) redirection constant - this is often/most likely set when a new ApiErrorCode obj (i.e. this class) is instantiated.
     * The ResponseAssembler will then take this and get the front-end URL from it and return this url to the client.
     */
    private $redirectCode;

    public function __construct(int $initialValue, string $clientMsg = null, string $redirectCode = null, $additionalStatusCodes = null)
    {
        $this->buildUCL($initialValue, $additionalStatusCodes);

        if (!empty($redirect)) {
            $this->redirectCode = $redirectCode;
        }

        // $clientMsg can only be empty if it has a "stdMessage". If not, throw an error.
        if (empty($clientMsg)) {
            $clientMsg = $this->getStandardResponseMsg();
        }

        parent::__construct($clientMsg);
    }

    /**
     * return a FrontendUrl constant representing the url that the client-side app should redirect to.
     */
    public function getRedirectionCode ()
    {
        if (empty($this->redirectCode)) {
            return BaseFrontendUrl::NO_REDIRECTION;
        } else {
            return $this->redirectCode;
        }
    }

    public function getHTTPStatusCode()
    {
        $stdResponse = $this->getPayload();

        if (!isset($stdResponse['HTTPStatusCode'])) {
            throw new \Exception ('ApiErrorCode->statusCode: '. $this->getValue() .' does not have a corresponding HTTPStatusCode. Please configure one at marker: #oollgg55');
        }
        return $stdResponse ['HTTPStatusCode'];
    }

    public function getStandardResponseMsg(): string
    {
        $stdResponse = $this->getPayload();
        if (!isset($stdResponse['msg'])) {
//            throw new \Exception('');
            throw new \Exception ('ApiErrorCode->statusCode: '. $this->getValue() .' does not have a corresponding "msg". Please configure one at marker: #oollgg55');
        }

        return $stdResponse ['msg'];
    }

    private function buildUCL($initialValue, $additionalStatusCodes)
    {
        $this->uniqueConstantsList = new UniqueConstantsList(
            'standard-response',
            [self::$initialStatusCodes, $additionalStatusCodes],
            $initialValue,
            'CMDKKD00'
        );
    }
}
<?php

namespace VisageFour\Bundle\ToolsBundle\Exceptions;

use Symfony\Component\HttpFoundation\Request;
use VisageFour\Bundle\ToolsBundle\Classes\ApiStatusCode\ApiStatusCodePayloadInterface;
use VisageFour\Bundle\ToolsBundle\Classes\UniqueConstantsList;
use VisageFour\Bundle\ToolsBundle\Interfaces\ApiErrorCodeInterface;
use VisageFour\Bundle\ToolsBundle\Interfaces\UniqueConstantsListInterface;
use VisageFour\Bundle\ToolsBundle\Services\BaseFrontendUrl;
use VisageFour\Bundle\ToolsBundle\Traits\HasAUniqueConstantsListTrait;

/**
 * -- Documentation --
 * https://docs.google.com/presentation/d/1WpWj80uAeQLJtNabbn-CxppNU-pEqteE5g222_qmjCo/edit#slide=id.gb2c1e3197b_0_1
 *
 * Extend this and implement application codes that are specific to your project.
 */
abstract class BaseApiErrorCode extends PublicException implements ApiErrorCodeInterface
{
    use HasAUniqueConstantsListTrait;

    /**
     * @var int|null
     * the FrontendUrl (class) redirection constant - this is often/most likely set when a new ApiErrorCode obj (i.e. this class) is instantiated.
     * The ResponseAssembler will then take this and get the front-end URL from it and return this url to the client.
     */
    private $redirectCode;

    /**
     * @var array
     * The context that is passed into logger->alert() when the exception is caught by the RA (response assembler).
     * This allows the exception to "pass down" to the RA and allow this $context information to be seen in the logger.
     */
    protected $loggerContext;

    /**
     * @var string[]
     * array keys to check when attempting to add new status codes to the UCL - ensuring the inputs are not mal-formed.
     *
     * only call parent::__construct() once the UCL has been populated with ALL statusCodes
     */
    private $expectedKeys;

    // use this when the app should use the exception message, not a "stdMessage" message
    // todo: throw an exception if a stdMessage exists but the exception constructor has a client messge - they both shouldn't exist, its confusing.
    const USE_EXCEPTION_MSG = 'MARKER#23Dzwdcfko2#FCW';

    // generic status codes (that will be shared will inheriting classes)
    const MIXED_RESPONSES                       = 5;        // this indicates that there will be multiple codes wrapped into this HTTP response. see ResponseCombiner class for more details.
    const OK                                    = 10;       // the default of a response, which indicates there were not problems.
    const INPUT_MISSING                         = 20;       // a GET or POST parameter is missing.

    /**
     * BaseApiErrorCode constructor.
     * @param $statusCode
     * @param array $codePayloads
     * @param string|null $loggerErrorMsg
     * @param string|null $redirectCode
     * @param array $loggerContext
     * @throws \Exception
     *
     * $statusCode is the internal "statusCode" that maps to a constant. This is *not* the HTTP status code. (however it will define a HTTP status code it expects - useful for testing)
     * $loggerErrorMsg will override the "standard" message (provided by the "statusCodes" classes)
     */
    public function __construct($statusCode, array $codePayloads, string $clientMsg = null, string $loggerErrorMsg = null, string $redirectCode = null, $loggerContext = [])
    {
        if (empty($statusCode)) {
            throw new \Exception('$statusCode is empty. Please set it to a valid value.');
        }
        $this->expectedKeys = ['msg', 'HTTPStatusCode'];

        $this->buildUCL();

        if (!empty($redirect)) {
            $this->redirectCode = $redirectCode;
        }

        $baseStatusCodes = [
            self::MIXED_RESPONSES                                    => ['msg'               => null,
                'HTTPStatusCode'    => 200],
            // security errors:
            self::OK                                    => ['msg'               => 'Request fine.',
                'HTTPStatusCode'    => 200],
            self::INPUT_MISSING                         => ['msg'               => 'You are missing an input parameter',
                'HTTPStatusCode'    => 400]
        ];

        $this->addArrayOfCodes($baseStatusCodes);

        $this->addArrayOfPayloads($codePayloads);

        $this->setUclValue($statusCode);
        $this->loggerContext = $loggerContext;

        // $clientMsg can only be empty if it has a "stdMessage". If not, throw an error.
        if (empty($loggerErrorMsg)) {
            $loggerErrorMsg = $clientMsg;
//            $loggerErrorMsg = $this->getStandardResponseMsg();
        }

        parent::__construct($clientMsg, $loggerErrorMsg);
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

//    // get the message that can be displayed to the user.
//    public function getUserMessage()
//    {
//        $stdResponse = $this->getPayload();
//
//        if ($stdResponse['msg'] == self::USE_EXCEPTION_MSG) {
//            // use the custom message configured in the exceptions constructor.
//            return $this->getMessage();
////            die ('use client message die()');
//        } else {
//            return $this->getStandardResponseMsg();
//        }
//        return $this->getPublicMsg();
//    }

    // get the stdMessage (in the const array above)
    public function getStandardResponseMsg(): string
    {
        $stdResponse = $this->getPayload();
//        dd($stdResponse);
        if (!isset($stdResponse['msg'])) {
//            throw new \Exception('');
            throw new \Exception ('ApiErrorCode->statusCode: '. $this->getValue() .' does not have a corresponding "msg". Please configure one at marker: #oollgg55');
        }

        return $stdResponse ['msg'];
    }

    // build the Unique Constants List (UCL)
    private function buildUCL()
    {
        $this->uniqueConstantsList = new UniqueConstantsList(
            'API status codes',
            'CMDKKD00'
        );
    }

    /**
     * @param array $codePayloads
     * Adds an array of payloads (of status codes) to the UCL
     */
    private function addArrayOfPayloads(array $codePayloads)
    {
        foreach($codePayloads as $curI => $curPayload) {
            $this->addStatusCodesPayload($curPayload);
        }
    }

    /**
     * @param ApiStatusCodePayloadInterface $apiStatusCodePayload
     * @throws \Exception
     *
     * Adds a payload of status codes to UCL - but does some additional checks (on the array elements / keys) first.
     */
    public function addStatusCodesPayload (ApiStatusCodePayloadInterface $apiStatusCodePayload)
    {
        $newCodes = $apiStatusCodePayload->getStatusCodes();
        if (empty($newCodes)) {
            $className = get_class($apiStatusCodePayload);
            throw new \Exception ('The Api status codes payload provided is empty (className: '. $className .')');
        }
        $this->addArrayOfCodes($newCodes);
    }

    /**
     * @param array $arr
     * @throws \Exception
     *
     * directly adds codes to the UCL
     */
    private function addArrayOfCodes(array $arr)
    {
        foreach ($arr as $internalCode => $responseSet) {
//            $this->checkIfKeyAlreadyExists($internalCode, $responseSet);
            $this->uniqueConstantsList->addListItem($responseSet, $internalCode);
        }
    }

    /**
     * @param array $expectedKeys
     * @param $internalCode
     * @param array $responseSet
     * @param ApiStatusCodePayloadInterface $apiStatusCodePayload
     * @return bool
     * @throws \Exception
     *
     * Check that 'msg' and 'HTTPStatusCode' keys exist
     */
//    private function checkIfKeyAlreadyExists ($internalCode, array $responseSet)
//    {
////        foreach ($this->expectedKeys as $curI2 => $curExpectedKey) {
//            if (empty($responseSet['msg'])) {
//                throw new \Exception('there is no element set for the api code: "msg" key (on array index: '. $internalCode .')');
//            }
////        }
//
//        return true;
//    }

    /**
     * @return array
     */
    public function getLoggerContext(): array
    {
        return $this->loggerContext;
    }
}
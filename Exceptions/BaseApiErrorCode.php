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
     * @var string[]
     * array keys to check when attempting to add new status codes to the UCL - ensuring the inputs are not mal-formed.
     *
     * only call parent::__construct() once the UCL has been populated with ALL statusCodes
     */
    private $expectedKeys;

    // use this when the app should use the exception message, not a "stdMessage" message
    // todo: throw an exception if a stdMessage exists but the exception constructor has a client messge - they both shouldn't exist, its confusing.
    const USE_EXCEPTION_MSG = 'MARKER#23Dzwdcfko2#FCW';

    /**
     * BaseApiErrorCode constructor.
     * @param $value
     * @param array $codePayloads
     * @param string|null $clientMsgOverride
     * @param string|null $redirectCode
     * @throws \Exception
     *
     * $statusCode is the internal "statusCode" that maps to a constant. This is *not* the HTTP status code. (however it will define a HTTP status code it expects - useful for testing)
     * $clientMsgOverride will override the "standard" message (provided by the "statusCodes" classes)
     */
    public function __construct($statusCode, array $codePayloads, string $clientMsgOverride = null, string $redirectCode = null)
    {
        if (empty($statusCode)) {
            throw new \Exception('$statusCode is empty. Please set it to a valid value.');
        }
        $this->expectedKeys = ['msg', 'HTTPStatusCode'];

        $this->buildUCL();

        if (!empty($redirect)) {
            $this->redirectCode = $redirectCode;
        }

        $this->addArrayOfPayloads($codePayloads);

        $this->setUclValue($statusCode);

        // $clientMsg can only be empty if it has a "stdMessage". If not, throw an error.
        if (empty($clientMsgOverride)) {
            $clientMsgOverride = $this->getStandardResponseMsg();
        }

        parent::__construct($clientMsgOverride);
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

    // get the message that can be displayed to the user.
    public function getUserMessage()
    {
        $stdResponse = $this->getPayload();

        if ($stdResponse['msg'] == self::USE_EXCEPTION_MSG) {
            // use the custom message configured in the exceptions constructor.
            return $this->getMessage();
//            die ('use client message die()');
        } else {
            return $this->getStandardResponseMsg();
        }
    }

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
     * Adds a payload of status codes to UCL - but does some additional checks (on the array elements / keys) first.
     */
    public function addStatusCodesPayload (ApiStatusCodePayloadInterface $apiStatusCodePayload)
    {
        foreach ($apiStatusCodePayload->getStatusCodes() as $internalCode => $responseSet) {
            $this->checkKeysExist($internalCode, $responseSet, $apiStatusCodePayload);

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
    private function checkKeysExist ($internalCode, array $responseSet, ApiStatusCodePayloadInterface $apiStatusCodePayload)
    {
        foreach ($this->expectedKeys as $curI2 => $curExpectedKey) {
            if (empty($responseSet['msg'])) {
                $className = get_class($apiStatusCodePayload);
                throw new \Exception('the $apiStatusCodePayload: "'. $className .'" has no element set for the "msg" key on array index: '. $internalCode);
            }
        }

        return true;
    }
}
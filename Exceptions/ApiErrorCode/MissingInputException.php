<?php

namespace VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode;

use App\Exceptions\ApiErrorCode;
use VisageFour\Bundle\ToolsBundle\Classes\ApiStatusCode\VFApiStatusCodes;

class MissingInputException extends ApiErrorCode
{
    public function __construct($paramName)
    {
        parent::__construct(
            VFApiStatusCodes::INPUT_MISSING,
            'You must provide a "'. $paramName .'" field as an GET/POST'
        );
    }
}
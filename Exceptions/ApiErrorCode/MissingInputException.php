<?php

namespace VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode;

use VisageFour\Bundle\ToolsBundle\Exceptions\BaseApiErrorCode;
use VisageFour\Bundle\ToolsBundle\Exceptions\PublicException;
use VisageFour\Bundle\ToolsBundle\Interfaces\ApiErrorCodeInterface;

class MissingInputException extends BaseApiErrorCode
{
    public function __construct($paramName)
    {
        parent::__construct(
            BaseApiErrorCode::INPUT_MISSING,
            'You must provide a "'. $paramName .'" field as an GET/POST'
        );
    }
}
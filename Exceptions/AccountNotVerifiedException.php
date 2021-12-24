<?php

namespace VisageFour\Bundle\ToolsBundle\Exceptions;

use App\Exceptions\ApiErrorCode;
use VisageFour\Bundle\ToolsBundle\Classes\ApiStatusCode\VFApiStatusCodes;

class AccountNotVerifiedException extends ApiErrorCode
{
    public function __construct()
    {
        parent::__construct(
            VFApiStatusCodes::ACCOUNT_NOT_VERIFIED
        );
    }
}
<?php

namespace VisageFour\Bundle\ToolsBundle\Exceptions;

use App\Twencha\Bundle\EventRegistrationBundle\Exceptions\ApiErrorCode;

class AccountNotVerifiedException extends ApiErrorCode
{
    public function __construct()
    {
        parent::__construct(
            ApiErrorCode::ACCOUNT_NOT_VERIFIED
        );
    }
}
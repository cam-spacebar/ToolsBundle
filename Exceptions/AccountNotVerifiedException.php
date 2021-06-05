<?php

namespace VisageFour\Bundle\ToolsBundle\Exceptions;

class AccountNotVerifiedException extends BaseApiErrorCode
{
    public function __construct()
    {
        parent::__construct(
            BaseApiErrorCode::ACCOUNT_NOT_VERIFIED
        );
    }
}
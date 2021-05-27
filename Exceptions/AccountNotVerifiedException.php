<?php

namespace VisageFour\Bundle\ToolsBundle\Exceptions;

class AccountNotVerifiedException extends PublicException
{
    public function __construct()
    {
        parent::__construct(
            'Cannot complete this request as this account is not verified. Please check your email (and spam folder) for an email containing a verification link.'
        );
    }
}
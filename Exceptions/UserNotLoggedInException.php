<?php

namespace VisageFour\Bundle\ToolsBundle\Exceptions;

use App\Services\FrontendUrl;
use App\Exceptions\ApiErrorCode;
use VisageFour\Bundle\ToolsBundle\Interfaces\ApiErrorCodeInterface;

class UserNotLoggedInException extends ApiErrorCode implements ApiErrorCodeInterface
{
    public function __construct()
    {
        $msg = 'You must log in to perform this function.';
        parent::__construct(
            ApiErrorCode::LOGIN_REQUIRED,
            $msg,
            FrontendUrl::LOGIN
        );
    }
}
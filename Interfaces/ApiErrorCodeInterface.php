<?php

namespace App\VisageFour\Bundle\ToolsBundle\Interfaces;

/*
 * this interface allows the app to throw (and catch) (less verbose and) custom exception classes, instead of the older (and more verbose):
 throw new ApiErrorCode(
            ApiErrorCode::LOGIN_REQUIRED,
            $msg,
            FrontendUrl::LOGIN
        );
 * It should also stop the need for transcribing specific exceptions into a general ApiErrroCode object via an intermediary try..catch block (which we were doing)
 */

interface ApiErrorCodeInterface
{

}
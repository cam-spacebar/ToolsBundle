<?php
/*
* created on: 29/11/2021 - 11:51
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode\UrlShortener;

use App\Exceptions\ApiErrorCode;
use VisageFour\Bundle\ToolsBundle\Classes\ApiStatusCode\VFApiStatusCodes;

class InvalidUrlShortCodeException extends ApiErrorCode
{
    public function __construct(string $code)
    {
        parent::__construct(
            VFApiStatusCodes::INVALID_SHORTENED_URL_CODE,
            'The URL you provided is not recognized in this system.'
        );
    }
}
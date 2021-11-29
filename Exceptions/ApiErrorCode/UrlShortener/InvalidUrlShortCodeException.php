<?php
/*
* created on: 29/11/2021 - 11:51
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode\UrlShortener;

use VisageFour\Bundle\ToolsBundle\Exceptions\BaseApiErrorCode;

class InvalidUrlShortCodeException extends BaseApiErrorCode
{
    public function __construct(string $code)
    {
        parent::__construct(
            BaseApiErrorCode::INVALID_SHORTENED_URL_CODE,
            'The URL you provided is not recognized in this system.'
        );
    }
}
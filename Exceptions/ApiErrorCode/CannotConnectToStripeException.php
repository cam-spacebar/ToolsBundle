<?php
/*
* created on: 29/10/2021 - 17:48
* by: Cameron
*/


namespace App\VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode;

use VisageFour\Bundle\ToolsBundle\Exceptions\BaseApiErrorCode;

class CannotConnectToStripeException extends BaseApiErrorCode
{
    public function __construct($reference)
    {
        parent::__construct(
            BaseApiErrorCode::CANNOT_CONNECT_TO_STRIPE,
            // provide error msg only, with "error:" or: "your card has not been charged".
            'Unable to connect to payment gateway.'
        );
    }
}
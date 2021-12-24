<?php
/*
* created on: 10/24/21
* by: cameronrobertburns 
*/

namespace App\VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode;


use App\Exceptions\ApiErrorCode;
use VisageFour\Bundle\ToolsBundle\Classes\ApiStatusCode\VFApiStatusCodes;
use VisageFour\Bundle\ToolsBundle\Exceptions\BaseApiErrorCode;

class PersonNotFoundException extends ApiErrorCode
{
    public function __construct($email)
    {
        parent::__construct(
            VFApiStatusCodes::INVALID_EMAIL_ADDRESS,
            'Person with email address: '. $email .' was not found.'
        );
    }
}
<?php
/*
* created on: 10/24/21
* by: cameronrobertburns 
*/

namespace App\VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode;


use VisageFour\Bundle\ToolsBundle\Exceptions\BaseApiErrorCode;

class PersonNotFoundException extends BaseApiErrorCode
{
    public function __construct($email)
    {
        parent::__construct(
            BaseApiErrorCode::INVALID_EMAIL_ADDRESS,
            'Person with email address: '. $email .' was not found.'
        );
    }
}
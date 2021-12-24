<?php
/*
* created on: 10/23/21
* by: cameronrobertburns 
*/


namespace App\VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode;

use App\Exceptions\ApiErrorCode;
use VisageFour\Bundle\ToolsBundle\Classes\ApiStatusCode\VFApiStatusCodes;

class InvalidCartTotalException extends ApiErrorCode
{
    public function __construct($providedTotal, $calculatedTotal)
    {
        parent::__construct(
            VFApiStatusCodes::INVALID_CART_TOTAL,
            'The $providedCart total of: '. $providedTotal .' is not the same as the $calculatedTotal of: '. $calculatedTotal
        );
    }
}
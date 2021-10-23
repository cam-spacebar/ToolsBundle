<?php
/*
* created on: 10/23/21
* by: cameronrobertburns 
*/


namespace App\VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode;

use VisageFour\Bundle\ToolsBundle\Exceptions\BaseApiErrorCode;

class InvalidCartTotalException extends BaseApiErrorCode
{
    public function __construct($providedTotal, $calculatedTotal)
    {
        parent::__construct(
            BaseApiErrorCode::INVALID_CART_TOTAL,
            'The $providedCart total of: '. $providedTotal .' is not the same as the $calculatedTotal of: '. $calculatedTotal
        );
    }
}
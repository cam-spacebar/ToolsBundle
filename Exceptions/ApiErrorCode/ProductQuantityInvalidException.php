<?php
/*
* created on: 10/23/21
* by: cameronrobertburns 
*/

namespace App\VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode;

use App\Exceptions\ApiErrorCode;
use VisageFour\Bundle\ToolsBundle\Classes\ApiStatusCode\VFApiStatusCodes;

class ProductQuantityInvalidException extends ApiErrorCode
{
    /**
     * ProductQuantityInvalidException constructor.
     * @param string $productReference
     * @param int $quantity
     */
     public function __construct(string $productReference, int $quantity)
    {
        parent::__construct(
            VFApiStatusCodes::PRODUCT_QUANTITY_INVALID,
            'The quantity: "'. $quantity .'" is an invalid quantity for product (with reference: "'. $productReference .'")'
        );
    }
}
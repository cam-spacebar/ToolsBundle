<?php
/*
* created on: 10/23/21
* by: cameronrobertburns 
*/

namespace App\VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode;

use VisageFour\Bundle\ToolsBundle\Exceptions\BaseApiErrorCode;

class ProductQuantityInvalidException extends BaseApiErrorCode
{
    /**
     * ProductQuantityInvalidException constructor.
     * @param string $productReference
     * @param int $quantity
     */
     public function __construct(string $productReference, int $quantity)
    {
        parent::__construct(
            BaseApiErrorCode::PRODUCT_QUANTITY_INVALID,
            'The quantity: "'. $quantity .'" is an invalid quantity for product (with reference: "'. $productReference .'")'
        );
    }
}
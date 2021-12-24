<?php

namespace VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode;

use App\Exceptions\ApiErrorCode;
use VisageFour\Bundle\ToolsBundle\Classes\ApiStatusCode\VFApiStatusCodes;

class InvalidProductReferenceException extends ApiErrorCode
{
    public function __construct($reference)
    {
        parent::__construct(
            VFApiStatusCodes::PRODUCT_REF_INVALID,
            'The product reference: "' . $reference . '" does not exist.'
        );
    }
}
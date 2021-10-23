<?php

namespace VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode;

use VisageFour\Bundle\ToolsBundle\Exceptions\BaseApiErrorCode;
use VisageFour\Bundle\ToolsBundle\Exceptions\PublicException;
use VisageFour\Bundle\ToolsBundle\Interfaces\ApiErrorCodeInterface;

class InvalidProductReferenceException extends BaseApiErrorCode
{
    public function __construct($reference)
    {
        parent::__construct(
            BaseApiErrorCode::PRODUCT_REF_INVALID,
            'The product reference: "' . $reference . '" does not exist.'
        );
    }
}
<?php
/*
* created on: 26/10/2021 - 17:14
* by: Cameron
*/

namespace App\VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode;

use Stripe\Error\InvalidRequest;
use VisageFour\Bundle\ToolsBundle\Exceptions\BaseApiErrorCode;

// an error that's triggered when a payment charge fails with the stripe SDK
class PaymentErrorException extends BaseApiErrorCode
{
    public function __construct(InvalidRequest $e)
    {
        parent::__construct(
            BaseApiErrorCode::STRIPE_PAYMENT_ERROR,
            'There was an error while attempting to charge your credit card. The payment processor error reads: '. $e->getMessage()
            . '. (Please note: your card has not been charged)'
        );
    }
}
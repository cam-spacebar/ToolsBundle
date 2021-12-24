<?php
/*
* created on: 26/10/2021 - 17:14
* by: Cameron
*/

namespace App\VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode;

use App\Exceptions\ApiErrorCode;
use Psr\Log\LoggerInterface;
use Stripe\Error\InvalidRequest;
use VisageFour\Bundle\ToolsBundle\Classes\ApiStatusCode\VFApiStatusCodes;

// an error that's triggered when a payment charge fails with the stripe SDK
class PaymentErrorException extends ApiErrorCode
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(InvalidRequest $e, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->logger->info('Stripe payment exception (Stripe\Error\InvalidRequest) caught. $e: ', [$e]);
        $errorMsg = $this->getStripeExceptionPublicErrorMsg($e);
        parent::__construct(
            VFApiStatusCodes::STRIPE_PAYMENT_ERROR,
            $errorMsg
            . '. (Please note: your card has not been charged)'
        );
    }

    // gets the stripe error code and returns a sanitized error message (or a generic error if the error code is not known)
    // note: this is useful because the stripe message provides too much information (we can't just return this to customers/users).
    private function getStripeExceptionPublicErrorMsg(InvalidRequest $e)
    {
        $jsonErr = $e->getJsonBody();
        $errorcode = $jsonErr['error']['code'];
        $this->logger->info('stripe error code: ', [$errorcode]);
        switch ($errorcode) {
            case 'token_already_used';
                $errerMsg = 'It appears that you are trying to double pay / pay twice';
                // stripe error message: 'You cannot use a Stripe token more than once: tok_1JoiO0It6yZjaEpjm9PccqMt.'
                break;
            default:
                $errerMsg = 'An unknown error has occured. Please refresh and try again';
                break;
        }
        $prefixMsg = 'There was an error while attempting to charge your credit card. ';

        return $prefixMsg.$errerMsg;
    }
}
<?php
/*
* created on: 23/12/2021 - 18:45
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Classes\ApiStatusCode;

use VisageFour\Bundle\ToolsBundle\Classes\ApiStatusCode\BaseApiStatusCodePayload;
use VisageFour\Bundle\ToolsBundle\Exceptions\BaseApiErrorCode;

/**
 * -- Documentation --
 * https://docs.google.com/presentation/d/1WpWj80uAeQLJtNabbn-CxppNU-pEqteE5g222_qmjCo/edit#slide=id.gb2c1e3197b_0_1
 *
 * Add a new ApiStatusCodes class (3Cols snippet):
 * https://3cols.com/board/COz2cm/10161/18738/38706
 */
class VFApiStatusCodes extends BaseApiStatusCodePayload
{
    // security error codes:
    const OK                                    = 10;       // the default of a response, which indicates there were not problems.
    const INPUT_MISSING                         = 20;       // a GET or POST parameter is missing.
    const INVALID_EMAIL_ADDRESS                 = 30;       // the email address provided is invalid.
    const ALREADY_LOGGED_IN                     = 40;       // when a user is attempting to login
    const ERROR_BUT_ALREADY_LOGGED_IN           = 43;       // originally it used the already logged in error (if logged in but failed authentication) - but
    // this could cause confusion so this error code was created
    const INVALID_ACCOUNT_VERIFICATION_TOKEN    = 50;
    const ACCOUNT_ALREADY_VERIFIED              = 60;
    const CHANGE_PASSWORD_TOKEN_INVALID         = 70;
    const INVALID_CREDENTIALS                   = 80;       // incorrect password
    const INVALID_NEW_PASSWORD                  = 90;       // when a user provides a new passwords that's too short or too long (for instance).
    const ACCOUNT_NOT_VERIFIED                  = 100;
    const LOGIN_REQUIRED                        = 110;
    const REDIRECT_301                          = 120;

    // purchase codes:
    const PRODUCT_REF_INVALID                   = 1110;
    const INVALID_CART_TOTAL                    = 1120;
    const PRODUCT_QUANTITY_INVALID              = 1130;
    const STRIPE_PAYMENT_ERROR                  = 1140;

    const CANNOT_CONNECT_TO_STRIPE              = 1201;

    // Url shortener related
    const INVALID_SHORTENED_URL_CODE            = 1300;

    public function __construct()
    {
        $this->statusCodes = [
            // security errors:
            self::OK                                    => ['msg'               => 'Request fine.',
                'HTTPStatusCode'    => 200],
            self::INPUT_MISSING                         => ['msg'               => 'You are missing an input parameter',
                'HTTPStatusCode'    => 400],
            self::INVALID_EMAIL_ADDRESS                 => ['msg'               => 'Email could not be found.',
                'HTTPStatusCode'    => 400],            // todo: should this be 401 for authentication related throws?!
            self::ALREADY_LOGGED_IN                     => ['msg'               => 'You are already logged in!',
                'HTTPStatusCode'    => 200],
            self::ERROR_BUT_ALREADY_LOGGED_IN           => ['msg'               => 'There was an error in your login attempt, however you are already logged in.',
                'HTTPStatusCode'    => 400],
            self::INVALID_ACCOUNT_VERIFICATION_TOKEN    => ['msg'               => 'The token provided is invalid. This account cannot be verified.',
                'HTTPStatusCode'    => 400],
            self::ACCOUNT_ALREADY_VERIFIED              => ['msg'               => 'Your account has already been verified.',
                'HTTPStatusCode'    => 400],
            self::CHANGE_PASSWORD_TOKEN_INVALID         => ['msg'               => 'The token provided is invalid. The password cannot be changed. Please re-try the "forgot your password" form to get a new link/token.',
                'HTTPStatusCode'    => 401],
            self::INVALID_CREDENTIALS                   => ['msg'               => 'Invalid credentials.',
                'HTTPStatusCode'    => 401],
            self::INVALID_NEW_PASSWORD                  => ['msg'               => 'The password provided is incorrect.',
                'HTTPStatusCode'    => 401],
            self::ACCOUNT_NOT_VERIFIED                  => ['msg'               => 'Cannot complete this request as this account is not verified. Please check your email (and spam folder) for an email containing a verification link.',
                'HTTPStatusCode'    => 401],        // message inherited from the exception class: AccountNotVerifiedException
            self::LOGIN_REQUIRED                        => ['msg'               => 'You must login first to view this page.',
                'HTTPStatusCode'    => 401],
            self::REDIRECT_301                          => ['msg'               => 'Being redirected.',
                'HTTPStatusCode'    => 301],

            // purchase codes:
            self::PRODUCT_REF_INVALID                   => ['msg'               => 'A product with the reference provided does not exist.',
                'HTTPStatusCode'    => 400],
            self::INVALID_CART_TOTAL                    => ['msg'               => 'The total provided does not match the one calculated on the backend',
                'HTTPStatusCode'    => 400],
            self::PRODUCT_QUANTITY_INVALID              => ['msg'               => 'Product quantity cannot be 0 or negative.',
                'HTTPStatusCode'    => 400],
            self::STRIPE_PAYMENT_ERROR                  => ['msg'               => BaseApiErrorCode::USE_EXCEPTION_MSG,
                'HTTPStatusCode'    => 400],
            self::CANNOT_CONNECT_TO_STRIPE              => ['msg'               => BaseApiErrorCode::USE_EXCEPTION_MSG,
                'HTTPStatusCode'    => 400],
            self::INVALID_SHORTENED_URL_CODE            => ['msg'               => BaseApiErrorCode::USE_EXCEPTION_MSG,
                'HTTPStatusCode'    => 400]
        ];

    }

}
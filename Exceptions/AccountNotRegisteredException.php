<?php

namespace VisageFour\Bundle\ToolsBundle\Exceptions;

class AccountNotRegisteredException extends PublicException
{
    public function __construct($email)
    {
        parent::__construct(
            'Cannot complete this request as this account has not been through and completed the registration process. Please complete the registration process and try again.',
            $msg = 'the user: '. $email .' must complete their registration before they can be verified.'
        );
    }
}
?>
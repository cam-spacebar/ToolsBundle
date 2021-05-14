<?php

namespace App\VisageFour\Bundle\ToolsBundle\Exceptions;

class AccountAlreadyVerified extends PublicException
{
    public function __construct()
    {
        $publicMsg =
            'This account has already been verified. You do not need to verify it again'
        ;
        parent::__construct($publicMsg);
        
    }
}
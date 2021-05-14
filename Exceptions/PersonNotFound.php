<?php


namespace App\VisageFour\Bundle\ToolsBundle\Exceptions;


class PersonNotFound extends PublicException
{
    public function __construct($email)
    {
        $publicMsg =
            'Person with email address: '. $email .' was not found.'
        ;
        parent::__construct($publicMsg);
    }
}
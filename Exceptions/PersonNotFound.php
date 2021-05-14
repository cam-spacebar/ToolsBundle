<?php


namespace App\VisageFour\Bundle\ToolsBundle\Exceptions;


class PersonNotFound extends \Exception
{
    public function __construct($email)
    {
        parent::__construct();

        $this->message =
            'Person with email address: '. $email .' was not found.'
        ;
    }
}
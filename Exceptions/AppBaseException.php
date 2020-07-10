<?php
/*
* created on: 02/07/2020 at 6:27 PM
* by: cameronrobertburns
*/

namespace App\VisageFour\Bundle\ToolsBundle\Exceptions;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

abstract class AppBaseException extends \Exception
{
    /**
     * This is the message that will be visible to the user (ussually via a flashbag)
     * as $this->message will contain additional information (object ids etc) that we don't want
     * shown to the user, but that we do want in a production error log.
     *
     * The publicMsg should be set in the inheriting exceptions __constructor
     *
     * @var string
     */
    protected $publicMsg;

    public function getPublicMessage()
    {
        return $this->publicMsg;
    }

    public function populateFlashBag (FlashBagInterface $fb)
    {
        $fb->add(
            'error',
            $this->publicMsg
        );
    }
}
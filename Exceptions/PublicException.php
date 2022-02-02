<?php

namespace VisageFour\Bundle\ToolsBundle\Exceptions;

/**
 * Class PublicException
 * @package VisageFour\Bundle\ToolsBundle\Exceptions
 *
 * allows the exception to provide a public message that is sent back to the user/client.
 */
class PublicException extends \Exception
{
    private $publicMsg;

    /**
     * PublicException constructor.
     * @param $publicMsg
     * @param null $exceptionMsg
     * if no "exceptionMsg" is provided, just use the $publicMsg as the default
     */
    public function __construct($publicMsg, $exceptionMsg = null)
    {
        $this->publicMsg = $publicMsg;

        if (empty($exceptionMsg)) {
            $exceptionMsg = $publicMsg;
        }

        parent::__construct($exceptionMsg);
    }

    /**
     * @return mixed
     */
    public function getPublicMsg()
    {
        return $this->publicMsg;
    }
}
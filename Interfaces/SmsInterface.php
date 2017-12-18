<?php

namespace VisageFour\Bundle\ToolsBundle\Interfaces;

/**
 * Created by PhpStorm.
 * User: CameronBurns
 * Date: 04/01/2017
 */

interface SmsInterface {
    public function getMessageText();

    function getRecipient ();
    function getOriginator ();
    function setSendFlag ($sendFlag);
}
?>
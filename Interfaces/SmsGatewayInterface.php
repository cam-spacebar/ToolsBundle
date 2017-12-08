<?php

namespace VisageFour\Bundle\ToolsBundle\Interfaces;

use Symfony\Component\HttpFoundation\Request;

/**
 * Created by PhpStorm.
 * User: CameronBurns
 * Date: 04/01/2017
 */

interface SmsGatewayInterface {
    // should return an array of the values of the object
    function SendSms(SmsInterface $sms);

    function getTo(Request $request);
    function getFrom(Request $request);
    function getMsgBody(Request $request);
}
?>
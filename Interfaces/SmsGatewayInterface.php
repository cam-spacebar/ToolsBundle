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

    // will return an SMS object based on the $request object that's passed in
    // each gateway implementation will have it's own way of creating this sms object
    function GetSmsFromRequest(Request $request);
}
?>
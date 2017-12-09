<?php

namespace VisageFour\Bundle\ToolsBundle\Services;

use Platypuspie\AnchorcardsBundle\Entity\SMS;
use Platypuspie\AnchorcardsBundle\Services\SMSManager;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Twilio\Rest\Client;
use VisageFour\Bundle\ToolsBundle\Interfaces\SmsGatewayInterface;
use VisageFour\Bundle\ToolsBundle\Interfaces\SmsInterface;

// Class acts as a wrapper for Twilio API
// used to send SMSes via twilio, the alternative is to use SMSCentral gateway or another classes
class TwilioGateway implements SmsGatewayInterface
{
    private $logger;
    /** @var \Platypuspie\AnchorcardsBundle\Services\CarrierNumberManager  */
    private $twilioClient;

    /**
     * TwilioGateway constructor.
     * @param Logger $logger
     * @param Client $twilioClient
     */
    public function __construct(Logger $logger, Client $twilioClient)
    {
        $this->logger               = $logger;
        $this->twilioClient         = $twilioClient;
    }

    public function getTo (Request $request) {
        return $request->get('To');
    }

    public function getFrom (Request $request) {
        return $request->get('From');
    }

    public function getMsgBody (Request $request) {
        return $request->get('Body');
    }

    /*
         * Twillio parameter names below:
        ToCountry=AU
        ToState=
        SmsMessageSid=SM262c9216286a86be5b95b18bfb235007
        NumMedia=0
        ToCity=
        FromZip=
        SmsSid=SM262c9216286a86be5b95b18bfb235007
        FromState=
        SmsStatus=received
        FromCity=
        Body=Hello
        FromCountry=AU
        To=%2B61439560703
        ToZip=
        NumSegments=1
        MessageSid=SM262c9216286a86be5b95b18bfb235007
        AccountSid=AC50299ab980feb8456b26066a4f1b561c
        From=%2B61449929558
        ApiVersion=2010-04-01
         */

    // this will send a SMS using the twilio API
    public function SendSms(SmsInterface $sms)
    {
        // Twilio API call
        $result = $this->twilioClient->account->messages->create(
            $sms->getRecipient(),
            array (
                'from' => $sms->getOriginator(),
                'body' => $sms->getMessageText()
            )
        );

/*
 * ORIGINAL script from promoter page to test sms. delete if it's all working.
//      Your Account SID and Auth Token from twilio.com/console

        $accountSid         = $this->getParameter('twillio_aus_account_one_account_sid');
        $testKeySid         = $this->getParameter('twillio_aus_account_one_api_sid');
        $testKeySecret      = $this->getParameter('twillio_aus_account_one_api_secret');

        //$client = new Client($testKeySid, $testKeySecret, $accountSid);

        $client = $this->container->get('anchorcards.twillio.account_one');

        $sms = $client->account->messages->create(
            '+61449929558',
            array(
                // Step 6: Change the 'From' number below to be a valid Twilio number
                // that you've purchased
                'from' => "+61439560703",

                // the sms body
                'body' => "Hey cameron, Monkey Party at 6PM. Bring Bananas!"
            )
        );
// */

        // todo: update this and give correct error message in logging if failed
        $sendSuccessful = true;

        if ($sendSuccessful) {
            return SMS::SENT_SUCCESSFULLY;
        } else {
            return SMS::ERROR_DURING_SEND;
        }
    }
}
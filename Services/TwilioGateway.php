<?php

namespace VisageFour\Bundle\ToolsBundle\Services;

use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Twilio\Rest\Client;
use VisageFour\Bundle\ToolsBundle\Interfaces\SmsGatewayInterface;
use VisageFour\Bundle\ToolsBundle\Interfaces\SmsInterface;

// Class acts as a wrapper for Twilio API
// used to send SMSes via twilio, the alternative is to use SMSCentral gateway or another classes
class TwilioGateway implements SmsGatewayInterface
{
    private $logger;
    private $twilioClient;

    public function __construct(Logger $logger, Client $twilioClient)
    {
        $this->logger       = $logger;
        $this->twilioClient = $twilioClient;
    }

    // this will send a SMS using the twilio API
    public function SendSms(SmsInterface $sms, $isSendingEnabled)
    {
        $this->logger->info('SMS message ready to send. Message: "'. $sms->getMessageText() .'" phone no.: "'. $sms->getRecipient() .'"');
        if (!$isSendingEnabled) {
            $this->logger->info('SMS sending disabled. SMS not sent');
        } else {
            $this->logger->info('Attempting to send SMS.');

            // Twilio API call
            $result = $this->twilioClient->account->messages->create(
                $sms->getRecipient(),
                array (
                    'from' => $sms->getOriginator(),
                    'body' => $sms->getMessageText()
                )
            );

            // todo: update this and give correct error message in logging if failed
            $sendSuccessful = true;

            if ($sendSuccessful) {
                $this->logger->info('SMS SENT');
            } else {
                $this->logger->info('SMS SEND UNSUCCESSFUL - more details should be provided here');
            }
        }

        return true;

    }

    // create an SMS object from the $request object passed in
    function GetSmsFromRequest(Request $request)
    {
        // todo: write any authentication needed

        // create new SMS for received SMS
        $sms = $this->HydrateSmsFromRequest ($request);

        return $sms;

    }

    function HydrateSmsFromRequest ($request) {
        // create from smsManager? but this will create a circular dependency

        // todo: use recepient to get CarrierNumber

        // ->setVendorValue        ($request->query->get('VALUE'))
        $recipientNumber = $request->query->get('XXX');

        //$carrierNumber = $this->carrierNumberManager->findxxx ($recipientNumber);

        $sms = '';

        die('not yet implemented');
        // todo: write method

        return $sms;
    }
}
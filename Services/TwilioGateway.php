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
    /** @var SMSManager $smsManager */
    private $smsManager;
    /** @var \Platypuspie\AnchorcardsBundle\Services\CarrierNumberManager  */
    private $carrierNumberManager;
    private $twilioClient;

    /**
     * TwilioGateway constructor.
     * @param Logger $logger
     * @param Client $twilioClient
     * @param Container $containerå
     */
    public function __construct(Logger $logger, Client $twilioClient, Container $container)
    {
        $this->logger               = $logger;
        $this->twilioClient         = $twilioClient;
        $this->smsManager           = $container->get('anchorcards.sms_manager');
        $this->carrierNumberManager = $container->get('anchorcards.carrier_number_manager');
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
        $to = $request->query->get('To');
        $carrierNumber = $this->carrierNumberManager->getCarrierNumberByNumber($to, true);

        $sms =  $this->smsManager->customCreateNew(
            $request->query->get('From'),
            new \DateTime('NOW'),
            $request->query->get('Body'),
            SMS::INBOUND,
            $to,
            $carrierNumber
        );

        //$this->flush();

        return $sms;
    }
}
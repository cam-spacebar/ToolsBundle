<?php

namespace VisageFour\Bundle\ToolsBundle\Services;

use Platypuspie\AnchorcardsBundle\Entity\SMS;
use VisageFour\Bundle\ToolsBundle\Interfaces\SmsGateway;

// Class acts as a wrapper for Twilio API
// used to send SMSes via twilio, the alternative is to use SMSCentral gateway or another classes
class SmsCentralGateway implements SmsGateway
{

// todo: organise methods below as they were pulled straight from the SMSManager (don't need to do this now as using TwilioGateway)


    public function GetSmsFromRequest ($request) {
        // authenticate
        $this->authenticateSMSCall(
            $request->query->get('USER_NAME'),
            $request->query->get('PASSWORD')
        );

        // create new SMS for received SMS
        $sms = $this->hydrateFromRequest ($request);

        $this->em->persist ($sms);
        $this->em->flush ($sms);

        $this->processSMSMessage ($sms);

        return $sms;
    }

    // authenticate external call to anchorcards
    public function authenticateSMSCall ($username, $password) {
        if ($username != 'cameronburns' || $password != '34089wcmoazl') {
            $this->logger->error('Failed to authenticate SMS call');

            $msg = 'Error when processing vendor SMS call: Authentication fail.';
            throw new \Exception ($msg);
        }
        $this->logger->info('Successfully authenticated SMS call');

        return $this;
    }

    // hydrate from SMSCentral request GET parameters
    public function hydrateFromRequest ($request, SMS $sms)
    {
        $sms = new SMS();
        //todo: update to use SMS manager to create new (will it create a circular dependency?)
        $sms->setOriginator         ($request->query->get('ORIGINATOR'))
            ->setRecipient          ($request->query->get('RECIPIENT'))
            ->setProvider           ($request->query->get('PROVIDER'))
            ->setCampaign           ($request->query->get('CAMPAIGN'))
            ->setMessageText        ($request->query->get('MESSAGE_TEXT'))
            ->setReference          ($request->query->get('REFERENCE'))
            ->setVendorValue        ($request->query->get('VALUE'))
            ->setMessageDirection   ('inbound')
            ->setReceiveDateTime    (new \DateTime('NOW'));

        // todo: use recepient to get CarrierNumber

        // get of create person (if does not exist)
        $this->getRelatedPersonOrCreate ($sms);

        return true;
    }



    // sends an SMS using the SMSCentral webhook (this is currently not used and twilio is used)
    public function SendSMS (SMS $sms) {
        $SMSvendorURL   = 'https://my.smscentral.com.au/api/v3.2/?'.
            'USERNAME='.                    urlencode($sms->getUserName()).
            '&PASSWORD='.                   urlencode($sms->getPassword()).
            '&ACTION=send&RECIPIENT='.      urlencode($sms->getRecipient()).
            '&MESSAGE_TEXT='.               urlencode($sms->getMessageText()).
            '&ORIGINATOR='.                 urlencode($sms->getOriginator());

        $this->logger->info('SMS message ready to send. Message: "'. $sms->getMessageText() .'" phone no.: "'. $sms->getRecipient() .'"');
        if (!$this->isSendingEnabled()) {
            $this->logger->info('SMS sending disabled. SMS not sent');
        } else {
            $this->logger->info('Attempting to send SMS.');
            $ch = curl_init($SMSvendorURL);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $curlOutput = curl_exec($ch);

            if($curlOutput != 0) {
                $errMsg = 'SMS Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch) .' - $curOutput: "'. $curlOutput .'"';
                $this->logger->info($errMsg);
                $this->logger->info('Is there any more information in this SMS error??: '. $curlOutput);
                throw new \Exception($errMsg);
            } else {
                $this->logger->info('SMS SENT');
            }

            curl_close($ch);
        }

        return true;
    }
}
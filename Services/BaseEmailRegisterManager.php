<?php

namespace VisageFour\Bundle\ToolsBundle\Services;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Twencha\Bundle\EventRegistrationBundle\Classes\AppSettings;
use VisageFour\Bundle\ToolsBundle\Interfaces\BaseEntityInterface;
use VisageFour\Bundle\ToolsBundle\Entity\EmailRegister;

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Swift_Mailer;
use VisageFour\Bundle\ToolsBundle\Traits\LoggerTrait;

abstract class BaseEmailRegisterManager
{
    use LoggerTrait;
    /*
                === USAGE BELOW! ===
    /** @var $emailRegisterManager EmailRegisterManager
    $emailRegisterManager = $this->container->get('app_name.email_register_manager');

    // $emailRegisterManager->sendNextSpooled();
    // $remaininedEmails = $emailRegisterManager->countSpooled();

    // spool emails to registrants
    foreach ($registrations as $curI => $curRC) {
        $emailRegister = $emailRegisterManager->createEmailAndProcess (
            'cameronrobertburns@gmail.com',
            array ('name' => 'dude'),
            'basic-email',
            'en',
            true,
            EmailRegister::essageFactor_ADAPTER
        );
    }

    === SUB-EMAIL REGISTER MANAGER CLASS - EMAIL METHOD IMPLEMENTATION EXAMPLE ===
    // CUSTOM EMAIL METHODS BELOW:
    // New Booking - admin notification
    public function sendNewBookingEmail (Booking $booking, Slug $slug, Event $event) {
        $person = $booking->getRelatedBookedPerson();
        $slug   = $booking->getRelatedSlug();

        $params = array (
            'bookingCreatedAt'      => $booking->getCreatedAt()->format('Y-m-j H:i'),
            'eventSeriesName'       => $slug->getRelatedEventSeries()->getName(),
            'eventStartDateTime'    => $event->getStartDateTime()->format('Y-m-j H:i'),

            'bookedPersonEmail'     => $person->getEmail(),
            'bookingId'              => $booking->getId(),

            'code'                  => $slug->getRelatedCode()->getCode(),
            'source'                => $slug->getRelatedSource()->getName()
        );

        $params = $this->duplicateCheck ($booking, $params);

        $template       = EmailRegisterManager::$AdminNewBookingEmail['reference'];
        $emailRegister  = $this->sendThis($slug->getRelatedPromoterPerson(), $params, $template);

        return $emailRegister;
    }
    */

    protected $emulateSending;

    /**
     * @var MailerInterface
     */
    protected $mailer;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var bool
     * when true, it will prevent a emails from being sent. This does not duplicate the function of env var: MAILER_DSN=null://default - both methods need to exists.
     * It is used (for example) to prevent sending verification emails when setting up new user fixtures (for test of loadFixtiures).
     */
    private $preventMailing;

    protected $adminEmail;

    /**
     *
     */
    public function __construct(EntityManager $em, MailerInterface $mailer, AppSettings $appSettings) {
        $this->preventMailing = false;

        $this->em = $em;
        $this->mailer = $mailer;

        $this->adminEmail = $appSettings->getSetting('adminEmailAddress');
    }

    public function setPreventMailing(bool $bool)
    {
        $this->preventMailing = $bool;
    }

    public function send (TemplatedEmail $email)
    {
        if ($this->preventMailing) {
            $msg='email prevented from sending as $this->preventMailing = true';
            $this->logger->info($msg);
        } else {
            $this->mailer->send($email);
        }

        return $this;
    }



    // spools an email for sending via a worker or sends it immediately (depending on apps config)
    public function createEmailAndProcess ($to, $params, $template, $locale, $spoolEmail, $adapter = EmailRegister::LEXIK_ADAPTER) {
        $loggerAction = ($spoolEmail) ? 'spool' : 'send';
        /** @var EmailRegister $email */
        $email = $this->createNew(false, false);
        $email->configure($to, $params, $template, $locale, $adapter);

        $this->logger->info('Attempting to '. $loggerAction .' email.'.
            '$to: "'. $to .'" $template: "'. $template .'"'.
            ' with id: '. $email->getId() .'. EmailRegister: (Id not available as just instantiated).');

        $email->setSendStatus(EmailRegister::SPOOLED);
        $msg = ' ';
        if ($spoolEmail == false) {
            $this->sendEmail($email);
        } else {
            $this->logger->info('EMAIL SPOOLED.');

        }

//        $this->logger->info($msg);
        $this->em->persist($email);
        $this->logObjCreation($email);

        return $email;
    }

    // send email if spooled
    public function sendEmail (EmailRegister $email) {
        if ($email->getSendStatus() == EmailRegister::SPOOLED) {
            if ($email->getAdapter() == EmailRegister::LEXIK_ADAPTER) {
                // create a swift message + send

//                $message = $this->lexikMailer->get(
//                    $email->getEmailTemplate(),
//                    $email->getToEmail(),
//                    $email->getParams(),
//                    $email->getLocale()
//                );

                $this->logger->info('Attempting to process (send) email. Template: "'. $email->getEmailTemplate() .'" To: "'. $email->getToEmail() .'"');

                // set to false when in dev mode and in particular when using mobile internet - as the send will fail
                $toSend = true;
                if ($toSend) {
                    // todo: create AppSettings emulate_email_sending variable?
                    // then send the email
                    if (!$this->zz) {
                        $this->logger->info('Email NOT sent to gateway (emulate_email_sending: true).');
                        $email->setSendStatus(EmailRegister::EMULATED_SEND);
                    } else {
                        $this->mailer->send($message);
                        $this->logger->info('Sent email successfully (emulate_email_sending: false).');
                        $email->setSendStatus(EmailRegister::SENT);
                    }

                } else {
                    $this->logger->info('EMAIL NOT SENT (Send email turned off - as mobile internet creates problems on dev machine). Email id: '. $email->getId());
                }
            } else {
                throw new \Exception ('Lexik email adapter not selected, no other email adapters known.');
            }
        } elseif ($email->getSendStatus() == EmailRegister::SENT) {
            throw new \Exception('Email with id: '. $email->getId() .' has already been sent');
        }

        return true;
    }

    /**
     * @return bool|EmailRegister
     *
     * chooses the next email to send and sends it
     * could order based on priotity fields or via a 'set' column to
     * process a particular batch of emails (will need to program these fields later on)
     */
    public function sendNextSpooled ()
    {
        // get a spooled email
        $email = $this->getSpooled();

        if (!empty($email)) {
            $this->sendEmail($email);
            return $email;
        }

        return false;
    }

    /**
     * @return null|EmailRegister
     * return a spooled email
     */
    public function getSpooled () {
        $email = $this->repo->findOneBy(array (
            'sendStatus'   => EmailRegister::SPOOLED
        ));

        return $email;
    }

    public function countSpooled () {
        return $this->repo->countSpooled();
    }

    public function getRemainingEmailstoSendAsCount () {
        $count      = $this->repo->countSpooled ();

        return $count;
    }

    /**
     * @return mixed
     */
    public function getEmulateSending()
    {
        return $this->emulateSending;
    }

    /**
     * @param mixed $emulateSending
     */
    public function setEmulateSending($emulateSending)
    {
        $this->emulateSending = $emulateSending;
    }
}
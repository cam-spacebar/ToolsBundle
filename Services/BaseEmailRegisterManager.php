<?php

namespace VisageFour\Bundle\ToolsBundle\Services;

use Lexik\Bundle\MailerBundle\Message\MessageFactory;
use VisageFour\Bundle\ToolsBundle\Entity\EmailRegister;

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Swift_Mailer;

class BaseEmailRegisterManager extends BaseEntityManager
{
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
        EmailRegister::LEXIK_ADAPTER
    );

    === SONATA ADMIN SERVICE DEFINITION ===
    SONATA ADMIN SERVICE:
        sonata.admin.email_register:
        class: Companyname\Bundle\Bundlename\Services\EmailRegisterManager -- class that extends BaseEmailRegisterManager
        tags:
            - name: sonata.admin
              manager_type: orm
              group: "Emails"
              label: "Registered Email"
        arguments:
            - ~
            - VisageFour\Bundle\ToolsBundle\Entity\EmailRegister
            - ~


    === EMAIL REGISTER MANAGER SERVICE ===
    app_name.email_register_manager:
        class: Companyname\Bundle\Bundlename\Services\EmailRegisterManager -- class that extends BaseEmailRegisterManager
        arguments:
            - "@doctrine.orm.entity_manager"
            - "ToolsBundle:EmailRegister" -- Entity should be extended first?? (probably no need / not many cases where extension is needed)
            - "@event_dispatcher"
            - "@logger"
            - "@lexik_mailer.message_factory"
            - "@mailer"
            - "%emulate_email_sending%

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

    /**
     * @var Swift_Mailer
     */
    protected $mailer;

    /**
     * @var MessageFactory
     */
    protected $lexikMailer;

    protected $emulateSending;

    /**
     * @return mixed
     */
    public function getLexikMailer()
    {
        return $this->lexikMailer;
    }

    /**
     * @param mixed $lexikMailer
     */
    public function setLexikMailer($lexikMailer)
    {
        $this->lexikMailer = $lexikMailer;
    }

    /**
     * @return mixed
     */
    public function getMailer()
    {
        return $this->mailer;
    }

    /**
     * @param mixed $mailer
     */
    public function setMailer($mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * BaseEntityManager constructor.
     * @param EntityManager             $em
     * @param                           $class
     * @param EventDispatcherInterface  $dispatcher
     * @param LoggerInterface           $logger
     * @param MessageFactory            $lexikMailer
     * @param Swift_Mailer              $mailer
     */
    public function __construct($em, $class, $dispatcher, $logger, $lexikMailer, $mailer, $emulateSending) {
        parent::__construct($em, $class, $dispatcher, $logger);

        $this->emulateSending = $emulateSending;
        if ($this->emulateSending) {
            $this->logger->info('Emulate email sending: ON');
        }

        $this->setMailer($mailer);
        $this->setLexikMailer($lexikMailer);
    }

    // spools an email for sending via a worker or sends it immediately (depending on apps config)
    public function createEmailAndProcess ($to, $params, $template, $locale, $spoolEmail, $adapter = EmailRegister::LEXIK_ADAPTER) {
        $loggerAction = ($spoolEmail) ? 'spool' : 'send';
        /** @var EmailRegister $email */
        $email = $this->createNew(false);
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

        $this->logger->info($msg);
        $this->em->persist($email);

        return $email;
    }

    // send email if spooled
    public function sendEmail (EmailRegister $email) {
        if ($email->getSendStatus() == EmailRegister::SPOOLED) {
            if ($email->getAdapter() == EmailRegister::LEXIK_ADAPTER) {
                // create a swift message + send

                $message = $this->lexikMailer->get(
                    $email->getEmailTemplate(),
                    $email->getToEmail(),
                    $email->getParams(),
                    $email->getLocale()
                );

                $this->logger->info('Attempting to process (send) email. Template: "'. $email->getEmailTemplate() .'" To: "'. $email->getToEmail() .'"');

                // set to false when in dev mode and in particular when using mobile internet - as the send will fail
                $toSend = true;
                if ($toSend) {
                    // then send the email
                    if ($this->emulateSending) {
                        $this->logger->info('Email NOT sent to gateway (emulate_email_sending: true).');

                    } else {
                        $this->mailer->send($message);
                        $this->logger->info('Sent email successfully (emulate_email_sending: false).');
                    }

                    $email->setSendStatus(EmailRegister::SENT);
                } else {
                    $this->logger->info('EMAIL NOT SENT (Send email turned off - as mobile internet creates problems on dev machine). Email id: '. $email->getId());
                }
            }
        } elseif ($email->getSendStatus() == EmailRegister::SENT) {
            throw new \Exception('Email with id: '. $email->getId() .' has already been sent');
        }

        return true;
    }

    // chooses the next email to send and sends it
    // could order based on priotity fields or via a 'set' column to
    // process a particular batch of emails (will need to program these fields later on)
    public function sendNextSpooled ()
    {
        // get a spooled email
        $email = $this->getSpooled();

        if (!empty($email)) {
            $this->sendEmail($email);
            return true;
        }

        return false;
    }

    // return a spooled email
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
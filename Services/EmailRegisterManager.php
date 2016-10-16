<?php

namespace VisageFour\Bundle\ToolsBundle\Services;

use Lexik\Bundle\MailerBundle\Message\MessageFactory;
use VisageFour\Bundle\ToolsBundle\Entity\EmailRegister;

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Swift_Mailer;

class EmailRegisterManager extends BaseEntityManager
{
    /*
                === USAGE BELOW! ===

    /** @var $emailRegisterManager EmailRegisterManager
    $emailRegisterManager = $this->container->get('anchorcards.email_register_manager');

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

    === SERVICE DEFINITION ===
    SONATA ADMIN SERVICE:
        sonata.admin.email_register:
        class: Platypuspie\AnchorcardsBundle\Admin\EmailRegisterAdmin
        tags:
            - name: sonata.admin
              manager_type: orm
              group: "Emails"
              label: "Registered Email"
        arguments:
            - ~
            - VisageFour\Bundle\ToolsBundle\Entity\EmailRegister
            - ~


    EMAIL REGISTER MANAGER SERVICE
    app_name.email_register_manager:
        class: VisageFour\Bundle\ToolsBundle\Services\EmailRegisterManager
        arguments:
            - "@doctrine.orm.entity_manager"
            - "ToolsBundle:EmailRegister"
            - "@event_dispatcher"
            - "@logger"
            - "@lexik_mailer.message_factory"
            - "@mailer"
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

    // these are the names of Lexik bundle templates and their twig
    // counterparts (as they need to be uploaded to the production server DB to work
    // todo: delete these - put them in a class that implements the ToolsBundle version in a project
    const PhotoGroupLinkEmail   = 'photoGroup-link';        // twig version: "PhotosReadyNotification.html.twig"
    const CodeRegistered        = 'code-registered';        // twig version: "CodesRegistered.twig"
    const MsgPhotocardNotice    = 'photocard-notice';       // twig version: "PhotocardNotice.twig"
    const DownloadOriginalPhoto = 'SendOriginalPhotoLink';  // twig version: "SendOriginalPhotoLink.twig"

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

    public function createEmailAndProcess ($to, $params, $template, $locale, $spoolEmail, $adapter = EmailRegister::LEXIK_ADAPTER) {
        $loggerAction = ($spoolEmail) ? 'spool' : 'send';
        /** @var EmailRegister $email */
        $email = $this->createNew(false);
        $email->configure($to, $params, $template, $locale, $adapter);

        $this->logger->info('Attempting to '. $loggerAction .' email with id: '. $email->getId() .'. EmailRegister: (Id not available as just instantiated). $to: "'. $to .'" $template: "'. $template .'"');

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
                        $this->logger->info('emulate sending: ON (email not sent to gateway).');
                    } else {
                        $this->mailer->send($message);
                        $this->logger->info('Sent email successfully (emulate sending set to: OFF).');
                    }

                    $email->setSendStatus(EmailRegister::SENT);
                } else {
                    $this->logger->info('EMAIL NOT SENT (Send email turned off - as mobile internet creates problems on dev machine). Email id: '. $email->getId());
                }
                $this->em->persist($email);
            }
        } elseif ($email->getSendStatus() == EmailRegister::SENT) {
            throw new \Exception('Email with id: '. $email->getId() .' has already been sent');
        }
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
}
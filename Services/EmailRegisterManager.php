<?php

namespace VisageFour\Bundle\ToolsBundle\Services;

use Lexik\Bundle\MailerBundle\Message\MessageFactory;
use VisageFour\Bundle\ToolsBundle\Entity\EmailRegister

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface
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
    EMAIL REGISTER SERVICE

    anchorcards.email_register_manager:
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
    private $mailer;

    /**
     * @var MessageFactory
     */
    private $lexikMailer;

    // these are the names of Lexik bundle templates and their twig
    // counterparts (as they need to be uploaded to the production server DB to work
    // todo: delete these or use as exmaples
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
    public function __construct($em, $class, $dispatcher, $logger, $lexikMailer, $mailer) {
        parent::__construct($em, $class, $dispatcher, $logger);

        $this->setMailer($mailer);
        $this->setLexikMailer($lexikMailer);
    }

    public function customCreateNew ($persist = true) {
        // instantiate
        $newObj = parent::createNew(false);

        // configure
        // ...

        if ($persist) {
            $this->persist($answer);
        }

        // log
        $persistStatus = ($persist) ? 'true' : 'false';
        $newObj = 'Created a new '. $this->class .' obj. Persist ('. $persistStatus .').';
        // custom notes
        $newObj =. ' with question caption:  "'.
        $question->getQuestionCaption() .'", for event series: "'.
        $eventSeries->getName() .'"'.

        $this->logString ($string);

        return $newObj;
    }

    public function createEmailAndProcess ($to, $params, $template, $locale, $spoolEmail, $adapter = EmailRegister::LEXIK_ADAPTER) {
        $loggerAction = ($spoolEmail) ? 'spool' : 'send';
        $email = $this->createNew();
        $email->configure($to, $params, $template, $locale, $adapter);

        $this->logger->info('Attempting to '. $loggerAction .' email with id: '. $email->getId() .'. EmailRegister: (Id not available as just instantiated). $to: "'. $to .'" $template: "'. $template .'"');

        $email->setSendStatus(EmailRegister::SPOOLED);
        $msg = ' ';
        if ($spoolEmail == false) {
            $this->sendEmail($email);
            $this->logger->info('EMAIL SENT.');
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

                $this->logger->info('Attempting to email. Template: "'. $email->getEmailTemplate() .'" To: "'. $email->getToEmail() .'"');

                // set to false when in dev mode and in particular when using mobile internet - as the send will fail
                $toSend = true;
                //$toSend = false;
                $emulateSending = true;
                if ($toSend || $emulateSending) {
                    // then send the email
                    if (!$emulateSending) {
                        $this->mailer->send($message);
                    }
                    $this->logger->info('Sent email successfully.');

                    $email->setSendStatus(EmailRegister::SENT);
                } else {
                    $this->logger->info('EMAIL NOT SENT (Send email turned off). Email id: '. $email->getId());
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
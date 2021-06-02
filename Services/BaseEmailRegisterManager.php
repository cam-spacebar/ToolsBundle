<?php

namespace VisageFour\Bundle\ToolsBundle\Services;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use VisageFour\Bundle\ToolsBundle\Entity\BasePerson;
use VisageFour\Bundle\ToolsBundle\Entity\EmailRegister;

use Doctrine\ORM\EntityManager;
use VisageFour\Bundle\ToolsBundle\Interfaces\FrontendUrlInterface;
use VisageFour\Bundle\ToolsBundle\Traits\LoggerTrait;

abstract class BaseEmailRegisterManager
{
    use LoggerTrait;

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

    /**
     * @var string
     */
    protected $adminEmail;

    /**
     * @var string
     */
    protected $businessName;

    /**
     * @var string
     */
    protected $automailerReplyAddress;

    /**
     * @var BaseFrontendUrl
     */
    protected $frontendUrl;

    /**
     *
     */
    public function __construct(
        EntityManager $em, MailerInterface $mailer, FrontendUrlInterface $frontendUrl, string $adminEmail, string $businessName, string $automailerReplyAddress
    ) {
        $this->preventMailing = false;

        $this->em = $em;
        $this->mailer = $mailer;

        $this->adminEmail   = $adminEmail;
        $this->businessName             = $businessName;
        $this->automailerReplyAddress   = $automailerReplyAddress;
        $this->frontendUrl = $frontendUrl;
    }

    protected function getSiteAdminAddress () {
        return new Address($this->adminEmail, 'Cameron');
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

    /**
     * @param string $email
     *
     */
    public function sendResetPasswordTokenEmail(BasePerson $person, string $token): bool
    {
        // must persist $person, as it's verification token has just changed.
        $this->em->persist($person);

        $passwordResetUrl = $this->frontendUrl->getFrontendUrl(
            BaseFrontendUrl::RESET_PASSWORD,
            [
                'email'                     => $person->getEmail(),
                'reset_password_token'      => $token,
            ],
            BaseFrontendUrl::FORMAT_ABSOLUTE
        );

        $email = (new TemplatedEmail())
            ->from(new Address($this->automailerReplyAddress, $this->businessName))
            ->to(new Address($person->getEmail(), $person->getFirstName()))
            ->subject('Password reset')
            ->htmlTemplate('@ToolsBundleEmails/resetPassword.html.twig')
            ->context([
                'person'                => $person,
                'passwordResetUrl'      => $passwordResetUrl
            ])
        ;

        $this->send($email);

        return true;
    }

    /**
     * @param string $email
     *
     * Send the user their account verification token (with registration complete notice).
     */
    public function sendRegistrationCompleteEmail(BasePerson $person): bool
    {
        // create a new verification token (even if one already exists)
        $verificationToken = $person->createVerificationHash();

        // must persist $person, as it's verification token has just changed.
        $this->em->persist($person);

        $account_verification_url = $this->frontendUrl->getFrontendUrl(
            BaseFrontendUrl::CONFIRM_EMAIL,
            [
                'email'                     => $person->getEmail(),
                'verification_token'        => $verificationToken,
            ],
            BaseFrontendUrl::FORMAT_ABSOLUTE
        );

        $email = (new TemplatedEmail())
            ->from(new Address($this->automailerReplyAddress, $this->businessName))
            ->to(new Address($person->getEmail(), $person->getFirstName()))
            ->subject('Please verify your account')
//            ->htmlTemplate('accountVerificationLink.html.twig')
            ->htmlTemplate('@ToolsBundleEmails/accountVerificationLink.html.twig')
            ->context([
                'person'                        => $person,
                'account_verification_url'      => $account_verification_url
            ])
        ;

        $this->send($email);

        return true;
    }

    // spools an email for sending via a worker or sends it immediately (depending on apps config)
    public function createEmailAndProcess ($to, $params, $template, $locale, $spoolEmail, $adapter = EmailRegister::LEXIK_ADAPTER) {
        WorkIsRequiredHere::needsRewriting();

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
        WorkIsRequiredHere::needsRewriting();

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
        WorkIsRequiredHere::needsRewriting();
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
        WorkIsRequiredHere::needsRewriting();
        $email = $this->repo->findOneBy(array (
            'sendStatus'   => EmailRegister::SPOOLED
        ));

        return $email;
    }

    public function countSpooled () {
        WorkIsRequiredHere::needsRewriting();
        return $this->repo->countSpooled();
    }

    public function getRemainingEmailstoSendAsCount () {
        WorkIsRequiredHere::needsRewriting();
        $count      = $this->repo->countSpooled ();

        return $count;
    }

    /*
     * IMPLEMENTATION:
        // create email + send email
        $url = $this->router->generate('displayPhotoGroup', array(
            'registeredCodeId' => $registeredCode->getId()),
            true);
        $params = array (
            'parameter1'   => $url
        );
        $template = EmailRegisterManager::adminBookingConfirm;
        $emailRegister = $this->sendThis($person, $params, $template);
    // */
    // the central method for sending emails to emailRegisterManager - checks person->email
    public function sendThis ($targetEmail, $params, $template, $spoolEmail = false, $locale = 'en') {
        $emailRegister = $this->createEmailAndProcess (
            $targetEmail,
            $params,
            $template,
            $locale,
            $spoolEmail,
            EmailRegister::LEXIK_ADAPTER
        );

        return $emailRegister;
    }
}
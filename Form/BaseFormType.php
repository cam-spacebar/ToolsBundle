<?php

namespace VisageFour\Bundle\ToolsBundle\Form;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormFactoryInterface;
use VisageFour\Bundle\ToolsBundle\Interfaces\CanNormalize;

class BaseFormType extends AbstractType
{
    /* implementation:
    TYPE CLASS:
    public function __construct(EntityManager $em, EventDispatcherInterface $dispatcher, LoggerInterface $logger) {
        parent::__construct($em, $dispatcher, $logger);
    }

    SERVICE DEFINITION:
    twencha.xxxx_form:
        class: Twencha\Bundle\EventRegistrationBundle\Form\xxxx
        arguments:
            - "@doctrine.orm.entity_manager"
            - "@event_dispatcher"
            - "@logger"
    // */

    // todo: convert const in inherited classes form name to a private member used here for use in webhook log notice - form name should be defined in the service definition

    protected $em;
    protected $dispatcher;
    protected $logger;
    protected $kernelEnv;
    protected $formFactory;
    protected $resultCodes;
    protected $webHookManager;

    protected $webHookURL;

    protected $formResult;

    protected $processingResult;

    private $webhookCallsDisabled;

    const FORM_NAME_HUMAN_READABLE = '';

    public function __construct(EntityManager $em, EventDispatcherInterface $dispatcher, LoggerInterface $logger, FormFactoryInterface $formFactory, $kernelEnv, $webHookManager = null, $disable_webhook_calls = false) {
        $this->em                       = $em;
        $this->dispatcher               = $dispatcher;
        $this->logger                   = $logger;
        $this->formFactory              = $formFactory;
        $this->kernelEnv                = $kernelEnv;
        $this->webHookManager           = $webHookManager;
        $this->webhookCallsDisabled     = $disable_webhook_calls;
    }

    public function setProcessingResult ($formResultCode) {
        if (empty($this->resultCodes[$formResultCode])) {
            $errorString = 'Code: "'. $formResultCode .'" could not be identified in the "'. self::FORM_NAME_HUMAN_READABLE .'" form';
            throw new \Exception($errorString);
        }

        $this->processingResult = $formResultCode;

        $this->logger->info(self::FORM_NAME_HUMAN_READABLE .' form processing result: "'. $this->resultCodes[$formResultCode] .'"');

        return $this->processingResult;
    }

    // this method is invoked when the controller does not recognise the formResult code returned by the Type object
    public function throwFormResultCodeError ($formResult) {
        $errorString = 'Controller is not programmed to handle a form result of: '. $formResult .' (this number will translate to a constant of some sort in the Type class used in this form)';
        throw new \Exception($errorString);
    }

    /**
     * @return mixed
     */
    public function getFormResult()
    {
        return $this->formResult;
    }

    /**
     * @param mixed $formResult
     */
    public function setFormResult($formResult)
    {
        $this->formResult = $formResult;
    }

    // if the webhook url is set, it will be called after when the form is succesfully persisted.
    // object passed in must support the CanNormalize interface
    public function callWebhook (CanNormalize $object1, $urlOverride = false) {
        if (!empty($urlOverride)) {
            $webHookTargetUrl = $urlOverride;
        } else {
            $webHookTargetUrl = $this->webHookURL;
        }

        if ($this->webhookCallsDisabled) {
            $this->logger->info('Webhook calls are disabled. Form webhook was not executed');
        } else {
            if (empty($this->webHookManager)) {
                $this->logger->info('Form: webHookManager is not populated when form tried to execute a webhook. The webhook was not called.');
                return true;
            } else {
                $normalizedObj = $object1->normalize();
                $jsonPacket = json_encode($normalizedObj);

                $result = true;
                if (!empty($webHookTargetUrl)) {
                    $result = $this->webHookManager->sendJson(
                        $this->webHookURL, $jsonPacket
                    );
                    $this->logger->info('Form: webHookManager sent a JSON packet to URL "' . $webHookTargetUrl . '"');
                }

                return $result;
            }
        }
    }
}
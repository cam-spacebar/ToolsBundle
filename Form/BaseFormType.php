<?php

namespace VisageFour\Bundle\ToolsBundle\Form;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormFactoryInterface;

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

    protected $em;
    protected $dispatcher;
    protected $logger;
    protected $kernelEnv;
    protected $formFactory;
    protected $resultCodes;

    protected $formResult;

    protected $processingResult;

    const FORM_NAME_HUMAN_READABLE = '';

    public function __construct(EntityManager $em, EventDispatcherInterface $dispatcher, LoggerInterface $logger, FormFactoryInterface $formFactory, $kernelEnv) {
        $this->em               = $em;
        $this->dispatcher       = $dispatcher;
        $this->logger           = $logger;
        $this->formFactory      = $formFactory;
        $this->kernelEnv        = $kernelEnv;
    }

    public function setProcessingResult ($formResultCode) {
        if (empty($this->resultCodes[$formResultCode])) {
            $errorString = 'Code: "'. $formResultCode .'" could not be identified in the "'. self::FORM_NAME_HUMAN_READABLE .'" form';
            //dump($this->resultCodes);
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
}
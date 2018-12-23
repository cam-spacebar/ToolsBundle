<?php

namespace VisageFour\Bundle\ToolsBundle\Form;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Intl\Exception\NotImplementedException;
use VisageFour\Bundle\ToolsBundle\Interfaces\CanNormalize;

/**
 * Class BaseFormType
 * @package VisageFour\Bundle\ToolsBundle\Form
 *
 * There's a number of different forms out there with different levels of sophistication. Below I've detailed these.
 * Try to use the most recent / advanced version of the form when constructing a new one.
 * Also refer to the list of 'forms constructed' as this may be a good base to start from when building a form.
 *
 * === Symfony form creation process: ===
 * https://docs.google.com/presentation/d/1xezcX-6mi3aqMWU-cYvqNoCMrMYSZ4qTRUD6AgTI_Y4/edit#slide=id.p
 *
 * Todo:
 * - automatically populate the flashBag - use an array that stores a standard message response to each result flag after form submission.
 *
 * ===== CLASS VERSIONS =====
 * v1.5 (search marker: FORM_CLASS_#1.5)
 * features:
 * - moved create form process to an external slide-deck
 * - added markers for each process step for quick referencing of the form class.
 *
 * v1.4 (search marker: FORM_CLASS_#1.4)
 * features:
 * - added handleSubmission() and processInput() processInput
 *
 * v1.3: (search marker: FORM_CLASS_#1.3)
 * features:
 * - added: populateFormInputs() and getDefaultData() to BaseFormType class
 *
 * v1.2: (search marker: FORM_CLASS_#1.2) changelog:
 * - added a form creation checklist
 * - uses self::class instead of a service definition parameter
 * - removed CLASS_NAME constant and replaced with $this->className
 *
 * v1.1: (search marker: FORM_CLASS_#1.1) changelog:
 * - constructor parameters changed (will make all forms using baseform incompatible)
 * - added createForm() method that uses $formPath to create the form.
 * - added form to baseFormType
 * - removed "$kernelEnv" - replaced with isDevEnvironment
 *
 * v1.0: (search marker: FORM_CLASS_#1)
 * features:
 * - result codes
 * - setProcessingResult() method
 * Example: Twencha:EventRegistrationBundle:EmailSignInType
 */
class BaseFormType extends AbstractType
{
    /* implementation:
    TYPE CLASS:
    public function __construct(EntityManager $em, EventDispatcherInterface $dispatcher, LoggerInterface $logger) {
        parent::__construct($em, $dispatcher, $logger);
    }

    SERVICE DEFINITION:
    twencha.yyy_form:
        class: Twencha\Bundle\EventRegistrationBundle\Form\yyy
        arguments:
            - "@doctrine.orm.entity_manager"
            - "@event_dispatcher"
            - "@logger"
    // */

    // Basic input processing flags. See the super class for other possibilities.
    const FORM_NOT_SUBMITTED            = 100;
    const SUCCESS                       = 200;

    protected $em;
    protected $dispatcher;
    protected $logger;
    protected $formFactory;
    protected $webHookManager;

    protected $webHookURL;

    protected $formResult;

    protected $resultCodes;         // array of possible result processingResults values
    protected $processingResult;    // result of processing a form that corresponds to a value within the resultCodes array.

    /**
     * @var Form
     */
    protected $form;

    /**
     * @var bool
     */
    protected $isDevEnvironment;

    /**
     * @var string
     */
    protected $className;

    private $webhookCallsDisabled;

    /**
     * @var string
     *
     * example:
     * 'Twencha\Bundle\EventRegistrationBundle\Form\BadgeValidationType'
     */
    protected $formPath;

    public function __construct(EntityManager $em, EventDispatcherInterface $dispatcher, LoggerInterface $logger, FormFactoryInterface $formFactory, $kernelEnv, $formPath, $webHookManager = null, $disable_webhook_calls = false) {
        $this->em                       = $em;
        $this->dispatcher               = $dispatcher;
        $this->logger                   = $logger;
        $this->formFactory              = $formFactory;
//        $this->kernelEnv                = $kernelEnv;
        $this->isDevEnvironment         = ($kernelEnv == 'dev') ? true : false;
        $this->formPath                 = $formPath;
        $this->webHookManager           = $webHookManager;
        $this->webhookCallsDisabled     = $disable_webhook_calls;

        $classPathPieces            = explode('\\', get_class($this));
        $this->className            = end($classPathPieces);
    }

    /**
     * @param Request $request
     * @return Form|\Symfony\Component\Form\FormInterface
     *
     * create form and handle request.
     * (this used to be in the controller)
     */
    public function createForm (Request $request, $options = array ()) {
        // getDefaultData is implemented in the super class
        $this->logger->info('populating form: "'. $this->className  .'" inputs with prefill/default values');
        $defaultData = $this->getDefaultData();


        $this->form = $this->formFactory->create($this->formPath, $defaultData, $options);

        $this->form->handleRequest($request);

        return $this->form;
    }

    public function getForm () {
        if (empty($this->form)) {
            throw new \Exception ('form has not been built. Please build the form before trying to get access to it.');
        }

        return $this->form;
    }

    /**
     * @param $formResultCode
     * @return mixed
     * @throws \Exception
     *
     * Checks the form result code provided is valid and
     * also logs the form processing result.
     */
    public function setProcessingResult ($formResultCode) {
        if (empty($this->resultCodes[$formResultCode])) {
            $errorString = 'Code: "'. $formResultCode .'" could not be identified in the "'. self::FORM_NAME_HUMAN_READABLE .'" form';
            throw new \Exception($errorString);
        }

        $this->processingResult = $formResultCode;

        $this->logger->info($this->className .' form processing result: "'. $this->resultCodes[$formResultCode] .'"');

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
        if (empty($this->webHookManager)) {
            throw new \Exception ('this form class ('. $this->className .' does not have a webhookManager');
        }
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
                        $webHookTargetUrl, $jsonPacket
                    );
                    $this->logger->info('Form: webHookManager sent a JSON packet to URL "' . $webHookTargetUrl . '"');
                }

                return $result;
            }
        }
    }

    /**
     * @return Form
     *
     * Populate the form with dev data and/or with prefill data determined by the super class (ussually from an object)
     */
    public function populateFormInputs()
    {
        // if the form has been submitted, get the form data from the POST vars - not here.
        if ($this->form->isSubmitted()) {
            return false;
        }

        if ($this->isDevEnvironment) {
            // (if in dev environment) test data will override any prefill data
            $this->logger->info('populating form: "'. $this->className  .'" with DEV environment testing values');
            $this->setFormTestDefaults();
        }

        return $this->form;
    }

    /**
     * override in the sub-class (if required) to pre-fill form input data with obj values.
     * This is used for cases where the form should have pre-existing values such as during an "edit" or a "back" button click.
     */
    protected function prefillInputs () {
//        $this->logger->info('populating form: "'. $this->className  .'" with prefill values');
//        $this->form->get('email')->setData('cameron@newtomelbourne.org');
    }

    // cannot implement as some super classes pass parameters.
//    public function handleFormSubmission () {
//        throw new \Exception ('handleFormSubmission() is deprecated. Please use handleSubmission() instead, which will in turn call: processInput() that must be implemented by your form\'s super class.');
//    }

    protected function processInput () {
        throw new NotImplementedException('processInput() must be overriden by the super class.');
    }

    /**
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     *
     * Used to process the form input data.
     * customer input handling logic is implemented in the super calss in this method: processInput: processInput()
     */
    public function handleSubmission () {
//        $this->processInput();      // the form input processing logic is implemented by the super class.

        if ($this->form->isSubmitted() && $this->form->isValid()) {
            $this->logger->info('form with class: '. $this->className .' isSubmitted and isValid. Now processing the form.');

            $processingResult = $this->processInput();
        } else {
            $processingResult = self::FORM_NOT_SUBMITTED;
        }

        return $this->setProcessingResult ($processingResult);
    }
}
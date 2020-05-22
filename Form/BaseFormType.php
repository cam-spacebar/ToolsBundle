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
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Intl\Exception\NotImplementedException;
use VisageFour\Bundle\ToolsBundle\Interfaces\CanNormalize;

/**
 * Class BaseFormType
 * @package VisageFour\Bundle\ToolsBundle\Form
 *
 * === Symfony form creation process: ===
 * Create a new form [P-CB-050]:
 * https://bit.ly/36m8s4i
 *
 */
class BaseFormType extends AbstractType
{
    // these are "result flags" (constants used by handleFormSubmission() to inform the controller what action to take)
    // they are used by: $formResult
    // See the super class for other possibilities (if they apply)
    const FORM_NOT_SUBMITTED            = 100;
    const SUCCESS                       = 200;
    const INVALID_INPUT                 = 300;

    protected $em;
    protected $dispatcher;
    protected $logger;
    protected $formFactory;
    protected $webHookManager;

    protected $webHookURL;

    // a flag to indicate the outcome of processInput() (in the super class)
    protected $formResult;

    // An array of strings that explain the error discovered in processInput() (in the super class)
    protected $processingErrors;

    protected $resultCodes;         // array of possible result processingResults values
    protected $processingResult;    // result of processing a form that corresponds to a value within the resultCodes array.

    protected $formData;

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

        $this->processingErrors     = array ();
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

    public function getProcessingResult()
    {
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
     * customer input handling logic is implemented in the super class via this method: processInput: processInput()
     *
     * the processInput() method will return a const value representing a "flag" for the outcome of the form
     * This "flag" is then interpreted by the caller to take appropriate action.
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

    /**
     * if there was processing errors while running processInput() in the child class
     * this will return true.
     */
    public function isHasErrors()
    {
        if (!empty($this->processingErrors)) {
            return true;
        }

        return false;
    }

    /**
     * if there was processing errors while running processInput() in the child class
     * this will return each of the errors (or error) as a string
     */
    public function getErrorsAsString ()
    {
//        dd($this->processingErrors);
        return implode(", \n", $this->processingErrors);
    }

    /**
     * populates a flashbag with each error in the form
     */
    public function populateFlashBagWithErrors (FlashBagInterface $flashBag)
    {
        if (!empty($this->processingErrors)) {
            foreach ($this->processingErrors as $curI => $curErrorMsg) {
                $flashBag->add('error', $curErrorMsg);
            }
        }

        return true;
    }

    /**
     * if there was processing errors while running processInput() in the child class
     * add an error message to the flash bag through this method
     * It adds the error line to monolog to help with error tracking on prod environments in particular
     *
     * set $errorFlag to null to prevent if from modifying the previously set processingResult value
     */
    public function addErrorMsg ($errorMsg, $fieldName, $errorFlag = self::INVALID_INPUT)
    {
        $submittedValue = $this->getFormValue($fieldName);

        if (!empty($this->processingErrors [$fieldName])) {
            throw new \Exception('Form: '. __CLASS__ .' is (currently) unable to handle multiple error lines for the same field.');
        }
        $this->processingErrors [$fieldName] = $errorMsg;

        $errorMsg = 'form field: '. $fieldName .' has the following error: "'. $fieldName .'". The sumitted value is: '. $submittedValue;
        $this->logger->error($errorMsg);

        // update error flag (but only if it's not null)
        if (!empty($errorFlag)) {
            $this->setProcessingResult($errorFlag);
        }

        return;
    }

    /**
     * @param $fieldName
     * @throws \Exception
     *
     * return the submitted field value from the form's data.
     * Throw a useful error if the form field does not exist (and tell the dev about available options)
     */
    protected function getFormValue ($fieldName) {
        // populate the formData value if not currently populated.
        if (empty($this->formData)) {
            $this->formData = $this->form->getData();
        }

        // attempt to get the field's value
        if (isset($this->formData[$fieldName])) {
            return $this->formData[$fieldName];
        } else {
            // throw a useful error if the fieldname doesn't exist.
            $formFieldOptions = "'". implode ("', '", array_keys($this->formData)) ."'";
            throw new \Exception("no form field with name: '". $fieldName ."' exists. The only available form fields are: ". $formFieldOptions);
        }
    }
}
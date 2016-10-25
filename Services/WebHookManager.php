<?php
/**
 * Created by PhpStorm.
 * User: cameronburns
 * Date: 30/01/2016
 * Time: 2:25 PM
 */

namespace VisageFour\Bundle\ToolsBundle\Services;

use Buzz\Browser;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;

class WebHookManager {

    private $em;
    private $buzz;
    private $logger;
    //private $serializer;

    public function __construct (EntityManager $em, Browser $buzz, LoggerInterface $logger, $disableWebhookCalls) {
        $this->em                   = $em;
        $this->buzz                 = $buzz;
        $this->logger               = $logger;
        $this->disableWebhookCalls  = $disableWebhookCalls;
        //todo: remove this if not needed - loojks like it is overwritten in the callWebhook() anyway and maybe an old aretfact
        //$this->serializer   = $serializer;
    }

    // todo: add variable for normalization attribute group - what is meant by this?
    /* IMPLEMENTATION CODE:
    /** @var WebHookManager $webHookManager
    $webHookManager = $this->container->get('toolsbundle.webhookmanager');

    $dateTimeFields = array ('createdAt', 'visaExpiry');    // array of the obj members that need to be converted into strings
    $result = $webHookManager->callWebhook (
        $job ['webHookURL'],
        $jobApplication,
        $dateTimeFields
    );

    // */
    public function callWebhook ($webhookURL, $object, $dateTimeFieldList = null) {
        //$serializer     = $this->serializer;
        /*

                 * // normalize only code
                $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
                $normalizer = new PropertyNormalizer($classMetadataFactory);
                $serializer = new Serializer([$normalizer]);

                $json = $serializer->normalize(
                    $person,
                    'json',
                    ['groups' => ['zapierSpredsheet']]
                );
                // */

        //$person = person::getPersonById(136, $em);
        //dump($person); die();\

        // encode entity into json
        $encoders = array(
            'xml' => new XmlEncoder(),
            'json' => new JsonEncoder()
        );

        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $propertyNormalizer = new PropertyNormalizer($classMetadataFactory);
        $fieldArray = $this->getFieldCallbacks($dateTimeFieldList);

        $propertyNormalizer->setCallbacks($fieldArray);

        $normalizers = array(
            $propertyNormalizer
            //new personNormalizer(),
            //new GetSetMethodNormalizer()
        );

        $serializer = new \Symfony\Component\Serializer\Serializer($normalizers, $encoders);

        $json = $serializer->serialize(
            $object,
            'json',
            ['groups' => ['zapierSpreadsheet']]
        );

        return $this->sendJson($webhookURL, $json);
    }

    // $newarray = array_merge(json_decode($json1, true),json_decode($json2, true));

    /**
     * @param $webhookURL
     * @param $json
     * @return bool|\Buzz\Message\MessageInterface
     */
    public function sendJson($webhookURL, $json)
    {
        $this->logger->info('About to send payload to webhook at URL: ' . $webhookURL);
        $this->logger->info('Payload JSON: ' . $json);

        // todo: marker: BUZZTIMEOUT: need to catch RequestException "Operation timed out after 5001 milliseconds with 0 bytes received" + send error to admin
        if ($this->disableWebhookCalls == false) {
            $response1 = $this->buzz->post(
                $webhookURL,
                array(),
                $json
            );
            $this->logger->info('JSON payload sent to webhook');

            return $response1;
        } else {
            $this->logger->info('JSON payload *NOT* sent to webhook. disableWebhookCalls set to true');

            return true;
        }
    }

    /**
     * This sets calls backs to execute when the normalizer finds an object within the object being normalized.
     * It's generally used for date time objects within the class being normalized
     *
     * @param $dateTimeFieldList
     * @return array
     */
    public function getFieldCallbacks($dateTimeFieldList)
    {
// used to convert datetime objects
        $callback = function ($dateTime) {
            if (!($dateTime instanceof \DateTime)) {
                throw new \Exception ('The \\DateTime object to be normalized is not a \\DateTime object');
            }

            return $dateTime instanceof \DateTime
                ? $dateTime->format(\DateTime::ISO8601)
                : '';
        };

        $fieldArray = array();
        if (!empty($dateTimeFieldList)) {
            // normalize DateTime objects
            foreach ($dateTimeFieldList as $curI => $fieldName) {
                $fieldArray [$fieldName] = $callback;
            }
            return $fieldArray;

            //dump()
        }
        return $fieldArray;
    }
}
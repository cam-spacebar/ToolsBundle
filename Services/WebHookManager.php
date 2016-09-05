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
use JMS\Serializer\Serializer;
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

    public function __constructor (EntityManager $em, Browser $buzz, LoggerInterface $logger) {
        $this->em           = $em;
        $this->buzz         = $buzz;
        $this->logger       = $logger;
        //todo: remove this if not needed - loojks like it is overwritten in the callWebhook() anyway and maybe an old aretfact
        //$this->serializer   = $serializer;
    }

    // todo: add variable for normalization attribute group - what is meant by this?
    public function callWebhook ($webhookURL, $object) {
        $buzz           = $this->buzz;
        //$serializer     = $this->serializer;
        $em             = $this->em;
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

        $response1 = $buzz->post(
            $webhookURL,
            array(),
            $json
        );

        //dump($response1); die();

        return $response1;
    }
}
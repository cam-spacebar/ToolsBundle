<?php

namespace VisageFour\Bundle\ToolsBundle\Services;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use NewInTown\NewInTownBundle\Entity\JobApplication;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twencha\Bundle\EventRegistrationBundle\Entity\Event;
use Twencha\Bundle\EventRegistrationBundle\Entity\EventSeries;
use Twencha\Bundle\EventRegistrationBundle\Entity\Slug;
use Twencha\Bundle\EventRegistrationBundle\Entity\Source;
use Twencha\Bundle\EventRegistrationBundle\Entity\Code;
use VisageFour\Bundle\ToolsBundle\Services\BaseEntityManager;

class SlugManager extends BaseEntityManager
{
    private $router;
    private $codeManager;

    /**
     * EventSeriesManager constructor.
     * @param EntityManager $em
     * @param $class
     * @param EventDispatcherInterface $dispatcher
     * @param Logger $logger
     */
    public function __construct(EntityManager $em, $class, EventDispatcherInterface $dispatcher, Logger $logger, Router $router, CodeManager $codeManager) {
        parent::__construct($em, $class, $dispatcher, $logger);

        $this->router       = $router;
        $this->codeManager  = $codeManager;
    }

    public function getSlugURL (Slug $slug, $routeName) {
        $URL = $this->router->generate(
            $routeName,
            array('slugCode' => $slug->getRelatedCode()->getCode()),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $URL;
    }

    /**
     * @return Slug
     */
    public function getSlugByCode ($slugCode) {
        $slug=null;
        try {
            if (empty($slugCode)) {
                throw new \Exception('No $slugCode specified');
            }

            $code = $this->codeManager->repo->findOneBy(
                array ('code' => $slugCode)
            );

            $slug = $this->repo->findOneBy(
                array ('relatedCode'    => $code)
            );

            if (empty($slug)) {
                throw new \Exception ('Could not find slug with code id: "'. $slugCode .'"');
            }
        } catch (\Exception $e) {
            //die ('Could not load event as there was an issue with the event slug in the link you have used.');
        }

        return $slug;
    }
}
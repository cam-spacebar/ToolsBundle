<?php

namespace VisageFour\Bundle\ToolsBundle\Services;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;
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
    public function getSlugByCode ($codeStr) {
        $code = $this->codeManager->findOneBy(array (
            'code'      => $codeStr
        ));

        if (empty($code)) { return null; }

        return $this->findOneBy(array (
            'relatedCode'  => $code
        ));
    }
}
<?php

namespace VisageFour\Bundle\ToolsBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use VisageFour\Bundle\ToolsBundle\Services\SiteManager;

// this listener is used to populate VisageFour/SiteManager
class CurrentSiteListener
{
    private $siteManager;

    private $em;

    private $host;

    private $baseHost;

    public function __construct(SiteManager $siteManager, EntityManager $em)
    {
        $this->siteManager  = $siteManager;
        $this->em           = $em;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request        = $event->getRequest();
        $this->host     = $request->getHost();

        // not currently setup for subdomains
        // $subdomain = str_replace('.'.$baseHost, '', $this->host);

        /** @var SiteManager $siteManager */
        $siteManager = $this->siteManager;
        //$siteManager->setCurrentSubdomain($subdomain);

        $domainName = strtolower(str_replace('www.', '', $this->host));
        $siteManager->setCurrentDomainName($domainName);
    }
}
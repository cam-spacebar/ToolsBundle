<?php

namespace VisageFour\Bundle\ToolsBundle\Services;

class SiteManager
{

    /*
     * implemenet with:
    services:
    site_manager:
        class: VisageFour\Bundle\ToolsBundle\Services\SiteManager

    current_site_listener:
        class: VisageFour\Bundle\ToolsBundle\EventListener\CurrentSiteListener
        arguments:
            - "@site_manager"
            - "@doctrine.orm.default_entity_manager"
            - "%base_host%"
        tags:
            -
                name: kernel.event_listener
                event: kernel.request
                method: onKernelRequest
     */
    private $currentSubdomain;
    private $currentDomainName;

    public function getCurrentSubdomain()
    {
        return $this->currentSubdomain;
    }

    public function setCurrentSubdomain($currentSubdomain)
    {
        $this->currentSubdomain = $currentSubdomain;
    }

    /**
     * @return mixed
     */
    public function getCurrentDomainName()
    {
        return $this->currentDomainName;
    }

    /**
     * @param mixed $currentDomainName
     */
    public function setCurrentDomainName($currentDomainName)
    {
        $this->currentDomainName = $currentDomainName;
    }
}
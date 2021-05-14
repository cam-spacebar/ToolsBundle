<?php
/*
* created on: 30/06/2020 at 10:53 PM
* by: cameronrobertburns
*/

namespace VisageFour\Bundle\ToolsBundle\Traits;

use Symfony\Component\Routing\Router;

trait RouterTrait
{
    /**
     * @var Router|null
     */
    private $router;

    /**
     * @required
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
    }

    protected function getRouter() : Router
    {
        $this->checkRouterIsSet();
        return $this->router;
    }

    private function checkRouterIsSet () {
        if (empty($this->router)) {
            throw new \Exception ("RouterTrait dependency: 'Router' has not been set. Please set it prior to using the: ". __CLASS__ ." class." );
        }
    }
}
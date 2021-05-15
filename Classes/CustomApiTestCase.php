<?php

namespace App\VisageFour\Bundle\ToolsBundle\Classes;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use VisageFour\Bundle\ToolsBundle\Services\TerminalColors;

abstract class CustomApiTestCase extends ApiTestCase
{
    static protected $terminalColors;

    abstract protected function getServices();

    static public function setUpBeforeClass(): void
    {
        //start the symfony kernel
        $kernel = static::createKernel();
        $kernel->boot();

        //get the DI container
        self::$container = $kernel->getContainer();

//        self::$terminalColors = self::$container->get('TerminalColors');
        self::$terminalColors = new TerminalColors();

        return;
    }

    public function outputRedTextToTerminal($msg)
    {
        $text = self::$terminalColors->getColoredString($msg, 'red', 'black');
        print "\n";
        print $text;
    }
}
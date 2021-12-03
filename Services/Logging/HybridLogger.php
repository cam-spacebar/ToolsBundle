<?php
/*
* created on: 03/12/2021 - 13:04
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Services\Logging;

use Psr\Log\LoggerInterface;

/**
 * Class HybridLogger
 * @package App\VisageFour\Bundle\ToolsBundle\Services\Logging
 *
 * Use overloading and dynamic method invocation to simulate inheritance.
 */
class HybridLogger
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger, LoggerInterface $console_logger)
    {
        die('incomplete! 34werfsdfwed');
//        dd($consoleLogger);
        $console_logger->info('asdf-zz');
//        die('333');

//        dd($consolelogger);

        $this->logger = $logger;
    }

    //
    public function __call($methodName, $args) {
//        dd($name, $arguments);
        if (method_exists ( $this->logger,  $methodName)) {
            call_user_func_array(array($this->logger, $methodName), $args);
        } else {
            $trace = debug_backtrace();
            trigger_error(
                'Undefined property via __call(): ' . $methodName .
                ' in ' . $trace[0]['file'] .
                ' on line ' . $trace[0]['line'],
                E_USER_NOTICE);
        }

    }

    public function info($msg, $context)
    {
        $args = func_get_args();
        $methodName = __FUNCTION__;
//        dd($methodName);
        call_user_func_array(array($this->logger, $methodName), $args);
    }
}
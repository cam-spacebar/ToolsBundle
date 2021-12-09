<?php
/*
* created on: 03/12/2021 - 13:04
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Services\Logging;

use Psr\Log\LoggerInterface;
use VisageFour\Bundle\ToolsBundle\Services\Debugging\ConsoleOutput;

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
    private $kernelEnv;

    /**
     * @var ConsoleOutput
     */
    private $consoleOutput;

    public function __construct(LoggerInterface $logger, ConsoleOutput $consoleOutput, $kernelEnv)
    {
        $this->logger = $logger;
        $this->kernelEnv = $kernelEnv;
        $this->consoleOutput = $consoleOutput;
    }

//    public function __call($methodName, $args) {
////        dd($name, $arguments);
//        if (method_exists ( $this->logger,  $methodName)) {
//            call_user_func_array(array($this->logger, $methodName), $args);
//        } else {
//            $trace = debug_backtrace();
//            trigger_error(
//                'Undefined property via __call(): ' . $methodName .
//                ' in ' . $trace[0]['file'] .
//                ' on line ' . $trace[0]['line'],
//                E_USER_NOTICE);
//        }
//
//    }

    public function info($msg, $context = [], $color = 'white_bold')
    {
        if ($this->kernelEnv == 'test') {
//           print $lb . $msg;
           $this->consoleOutput->outputColoredTextToTerminal($msg, $color);
           if ($context != []) {
//               dump($context);
           }
        } else {        // prod or dev env
            $this->logger->info($msg, $context);
        }
//        $args = func_get_args();
//        $methodName = __FUNCTION__;
//        dd($methodName);
//        call_user_func_array(array($this->logger, $methodName), $args);
    }

    // prints: "==== $header ===="
    // useful for loops - to indicate new section
    public function sectionHeader ($header) {
        $text = "==== ". $header ." ====";
        $this->info($text, [], 'purple');
    }
}
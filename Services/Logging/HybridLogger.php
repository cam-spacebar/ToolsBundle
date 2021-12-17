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
        $prefix = $this->getPrefix();
        if ($this->kernelEnv == 'test') {
            $this->consoleOutput->outputColoredTextToTerminal($msg, $prefix, $color);
            if ($context != []) {
//               dump($context);
            }
        } else {        // prod or dev env
//            print $msg."\n";
            $this->logger->info($prefix . $msg, $context);
        }
//        $args = func_get_args();
//        $methodName = __FUNCTION__;
//        dd($methodName);
//        call_user_func_array(array($this->logger, $methodName), $args);
    }

    // if env is test, output $msg to console. This is mainly for writing automated tests and fixtures.
    private function outputToConsole ($msg, $context = [], $color = 'white_bold', $prefixOverride = null) {

        if ($this->kernelEnv == 'test') {
            $prefix = $this->getPrefix($prefixOverride);
            $this->consoleOutput->outputColoredTextToTerminal($msg, $prefix, $color);

            if ($context != []) {
//               dump($context);
            }
        }
    }

    public function alert($msg, $context = [], $color = 'white_bold')
    {
        $this->outputToConsole($msg, $context, $color, 'alert');
        $prefix = $this->getPrefix();
        if (!$this->kernelEnv == 'test') {
            $this->logger->alert($prefix . $msg, $context);
        }
    }

    public function sectionHeader ($header) {
        $text = "==== ". $header ." ====";
        $this->info($text, [], 'purple');
    }

    public function consoleLineBreak()
    {
        if ($this->kernelEnv == 'test') {
            print "\n";
        }
    }

    public function addLogPrefix($prefix)
    {
        if (!empty($this->prefix)) {
            throw new \Exception('you must clear the prefix first before adding a new one.');
        }
        $this->prefix = $prefix;
    }

    public function clearLogPrefix()
    {
        $this->prefix = '';
    }

    /*
     * a $prefixOverride can be used to replace an existing prefix string in the console.
     * This is used to signal the log type (when it's not info) e.g. "alert"
     */
    private function getPrefix($prefixOverride = null)
    {
        if (!empty($prefixOverride)) {
            return $prefixOverride;
        }
        if (!empty($this->prefix)) {
            return '['. $this->prefix .'] ';
        }

        return '';
    }

    /**
     * @param \Throwable $e
     *
     * displays an exception - in test env only.
     * it was created as dump() on exceptions, unclear and too verbose.
     */
    public function displayException(\Throwable $e)
    {
        if ($this->isTestEnv()) {
            $this->alert('Exception caught: '. $e->getMessage(), [], 'red');
            $this->alert('For more info, goto marker: #messageDump and uncomment the dump()', []);
//            dump($e);
        }
    }

    private function isTestEnv()
    {
        return ($this->kernelEnv == 'test');
    }
}
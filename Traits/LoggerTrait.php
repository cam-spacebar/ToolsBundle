<?php
/*
* created on: 03/06/2020 at 4:47 PM
* by: cameronrobertburns
*/

namespace VisageFour\Bundle\ToolsBundle\Traits;

use PHPUnit\Runner\Exception;
use Psr\Log\LoggerInterface;
use VisageFour\Bundle\ToolsBundle\Services\Logging\HybridLogger;

trait LoggerTrait
{
    /**
     * @var HybridLogger
     */
    protected $logger;

    /**
     * @var string
     *
     * adds more context to the log messages for instance:
     * "[MessageQue] New entity created: File"
     * the [MessageQue] text is the prefix - that indicates the log is in the MessageQue]
     *
     * Adding a prefix (between two points of code) is also useful for seeing everything that's being executed between those two points.
     */
    private $prefix;

    /**
     * @required
     * note: required is what tells symfony to call this and inject $logger
     */
    public function setLogger(HybridLogger $logger)
    {
        $this->logger = $logger;
    }

    private function logInfo(string $message, array $context = [])
    {
        $this->checkLoggerIsSet();
        $this->logger->info( $message, $context);
    }

    private function checkLoggerIsSet () {
        if (empty($this->logger)) {
            throw new \Exception ("LoggerTrait dependency: 'Logger' has not been set. Please set it prior to using the: ". __CLASS__ ." class." );
        }
    }
}
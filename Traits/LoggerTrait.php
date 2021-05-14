<?php
/*
* created on: 03/06/2020 at 4:47 PM
* by: cameronrobertburns
*/

namespace VisageFour\Bundle\ToolsBundle\Traits;

use Psr\Log\LoggerInterface;

trait LoggerTrait
{
    /**
     * @var LoggerInterface|null
     */
    protected $logger;

    /**
     * @required
     * note: required is what tells symfony to call this and inject $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    private function logInfo(string $message, array $context = [])
    {
        $this->checkLoggerIsSet();
        $this->logger->info($message, $context);
    }

    private function checkLoggerIsSet () {
        if (empty($this->logger)) {
            throw new \Exception ("LoggerTrait dependency: 'Logger' has not been set. Please set it prior to using the: ". __CLASS__ ." class." );
        }
    }
}
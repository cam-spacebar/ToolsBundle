<?php
/*
* created on: 02/02/2022 - 19:31
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Services\ApiStatusCode;

use VisageFour\Bundle\ToolsBundle\Interfaces\ApiErrorCodeInterface;
use VisageFour\Bundle\ToolsBundle\Traits\LoggerTrait;

/**
 * Class LogException
 * @package App\VisageFour\Bundle\ToolsBundle\Services\ApiStatusCode
 *
 * log details of a ApiStatusCode Exception to the logger
 */
class LogException
{
    use LoggerTrait;

    public function __construct()
    {

    }

    public function run (ApiErrorCodeInterface $e)
    {
        $this->logger->info("Exception caught, class name: '". get_class($e) ."'");
        $context = $e->getLoggerContext();
        $this->logger->info("Exception message: ". $e->getMessage(), $context);
    }

}
<?php
/*
* created on: 30/05/2020 at 3:55 PM
* by: cameronrobertburns
*/

namespace VisageFour\Bundle\ToolsBundle\Services;

use VisageFour\Bundle\ToolsBundle\Interfaces\BaseEntityInterface;
use Psr\Log\LoggerInterface;
use VisageFour\Bundle\ToolsBundle\Entity\BaseEntity;

/**
 * Class LoggingExtraData
 * @package App\VisageFour\Bundle\ToolsBundle\Classes
 *
 * [Overview:] get the logging data from a $obj, complete checks
 * normalize logging data and return it to the client. Ussually
 * for the purpose of creating a log record about an entity.
 *
 * [CRC readme:] This code is part of a Custom Reusable Component (CRC),
 * you can learn more about is via it’s CRC readme here:
 * https://bit.ly/2XIrgab
 */
class LoggingExtraData
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    private $classname;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger   = $logger;
    }
}
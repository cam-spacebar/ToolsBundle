<?php
/*
* created on: 16/12/2021 - 12:12
* by: Cameron
*/

namespace App\VisageFour\Bundle\ToolsBundle\Classes\Messenger;

use VisageFour\Bundle\ToolsBundle\Interfaces\Messenger\EntityMessageInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use VisageFour\Bundle\ToolsBundle\Services\Logging\HybridLogger;
use VisageFour\Bundle\ToolsBundle\Traits\LoggerTrait;

/**
 * Class BaseEntityHandler
 * @package App\VisageFour\Bundle\ToolsBundle\Classes\Messenger
 *
 * provides nice, automatic start and end logging for messages
 * only for use with "BaseEntityMessage" extending messages
 */
abstract class BaseEntityHandler implements MessageHandlerInterface
{
    use LoggerTrait;

    abstract protected function runProcess(int $id);

    public function __construct()
    {
    }

    /**
     * @param EntityMessageInterface $msg
     * @return bool
     * this should be called by the inheriting class: __invoke message.
     * adds useful, auto start and end message logging and calls main message logic (via: runProcess)
     */
    public function handleMessage(EntityMessageInterface $msg)
    {
        $id = $msg->getId();

        $this->logStart($msg);
        try {
            $this->runProcess($id);
        } catch (\Throwable $e) {
            $this->logger->displayException($e);
        }

        $this->logEnd($msg);

        return true;
    }

    private function logStart(EntityMessageInterface $message)
    {
        $this->logger->sectionHeader(
            'Start handling "'. $message->getMessageClassName() .'" CMD-MSG. '.
            'Entity class: '. $message->getEntityClassName() .' (id: '. $message->getId() .')'
        );
    }

    private function logEnd(EntityMessageInterface $message)
    {
        $this->logger->sectionHeader('Finished handling "'. $message->getMessageClassName() .'" CMD-MSG');

        // (note: extra line breaks are needed for messenger:consume - as the dumps() consume uses will otherwise be on the same line)
        $this->logger->consoleLineBreak();
        $this->logger->consoleLineBreak();
    }
}
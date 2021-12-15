<?php
/*
* created on: 10/12/2021 - 17:16
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Services\Message;

use Symfony\Component\Messenger\MessageBusInterface;
use VisageFour\Bundle\ToolsBundle\Traits\LoggerTrait;
use Symfony\Component\Messenger\Envelope;

// This service simply adds a log each time a message is dispatched.
class LoggedMessageBus
{
    use LoggerTrait;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function dispatch($message, array $stamps = []): Envelope
    {
        $shortName = (new \ReflectionClass($message))->getShortName();
        $this->logger->info('Message dispatched. (Message class: '. $shortName .')', $message, 'cyan');

        $this->logger->addLogPrefix('MessageQue');
        $result = $this->messageBus->dispatch($message, $stamps);
        $this->logger->clearLogPrefix('MessageQue');

        return $result;
    }
}
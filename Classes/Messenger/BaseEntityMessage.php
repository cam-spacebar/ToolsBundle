<?php
/*
* created on: 16/12/2021 - 11:39
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Classes\Messenger;


use VisageFour\Bundle\ToolsBundle\Interfaces\BaseEntityInterface;
use VisageFour\Bundle\ToolsBundle\Interfaces\Messenger\EntityMessageInterface;
use VisageFour\Bundle\ToolsBundle\Message\BaseMessage;

/**
 * Class BaseEntityMessage
 * @package VisageFour\Bundle\ToolsBundle\Classes\Messenger
 *
 * an entityMessage is used for messages with a single entity that has a Id (which is most messages)
 * it was designed to (enforce) providing of additional information so we can get automated logging (that is displayed when using message:consume)
 *
 * Obviously don't use this class if your message does not use an ORM entity.
 *
 * Example: https://docs.google.com/presentation/d/1T6wdT9HThtU8kcYCUwapnLlQodbW3bGiCe-5l-772X8/edit#slide=id.p
 */
class BaseEntityMessage extends BaseMessage implements EntityMessageInterface
{
    private $id;

    private $entityClassName;
    private $messageClassName;

    public function __construct (BaseEntityInterface $entity)
    {
        $id = $entity->getId();
        $this->checkEntityIDIsNotEmpty($id);
        $this->id = $id;

        $this->messageClassName = (new \ReflectionClass($this))->getShortName();
        $this->entityClassName = $entity->getShortName();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEntityClassName()
    {
        return $this->entityClassName;
    }

    /**
     * @return string
     */
    public function getMessageClassName()
    {
        return $this->messageClassName;
    }
}
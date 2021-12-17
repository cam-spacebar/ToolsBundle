<?php
/*
* created on: 16/12/2021 - 12:04
* by: Cameron
*/


namespace VisageFour\Bundle\ToolsBundle\Interfaces\Messenger;


interface EntityMessageInterface
{
    public function getId();
    public function getEntityClassName();
    public function getMessageClassName();
}
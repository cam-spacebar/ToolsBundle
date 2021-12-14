<?php
/*
* created on: 13/12/2021 - 20:18
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Message;

abstract class BaseMessage
{
    protected function checkEntityIDIsNotEmpty($id) {
        if (empty($id)) {
            throw new \Exception('unable to create que message, as the entity id was empty. Maybe there\'s no $em->flush()?');
        }
    }
}
<?php
/*
* created on: 30/05/2020 at 12:25 PM
* by: cameronrobertburns
*/

namespace VisageFour\Bundle\ToolsBundle\Interfaces;

interface BaseEntityInterface
{
    public function getLoggingData(int $detailLevel);
}
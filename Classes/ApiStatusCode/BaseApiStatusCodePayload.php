<?php
/*
* created on: 23/12/2021 - 14:56
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Classes\ApiStatusCode;

abstract class BaseApiStatusCodePayload implements ApiStatusCodePayloadInterface
{
    protected $statusCodes;

    /**
     * @return array[]
     */
    public function getStatusCodes(): array
    {
        return $this->statusCodes;
    }

    public function __construct()
    {
    }
}
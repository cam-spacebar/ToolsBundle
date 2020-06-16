<?php
/*
* created on: 16/06/2020 at 9:45 PM
* by: cameronrobertburns
*/

namespace VisageFour\Bundle\ToolsBundle\Exceptions;


use VisageFour\Bundle\ToolsBundle\Classes\BaseFlagger;
use VisageFour\Bundle\ToolsBundle\Statics\StaticInternational;

class FlagOptionDoesNotExistException extends \Exception
{

    public function __construct($flaggerName, $flagValue, array $flagOptions)
    {
        parent::__construct();

        $optionsString = BaseFlagger::stringifyAllFlagOptions($flagOptions);

        $this->message =
            $flaggerName .' flag with value: '. $flagValue .' does not have a string equivalent configured'.
            ', so we cannot retrieve it. Check that you have populated the $flagsToText array correctly. Your'.
            ' current flag options are: '. $optionsString .'.';
        ;
    }
}
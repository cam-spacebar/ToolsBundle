<?php
/*
* created on: 09/06/2020 at 3:32 PM
* by: cameronrobertburns
*/


namespace VisageFour\Bundle\ToolsBundle\Exceptions;


use VisageFour\Bundle\ToolsBundle\Statics\StaticInternational;

class LanguageCodeDoesNotExist extends \Exception
{
    private $stageNumber;

    public function __construct($langCode)
    {
        $this->langCode = $langCode;
        parent::__construct();

        $countries = StaticInternational::getCountriesArray();

        if (!empty($countries[$langCode])) {
            $this->message =
                'it looks like you have submitted a $country code (code: "'.
                $langCode .'" for "'. StaticInternational::getCountryNameByCode($langCode) .'") '.
                'when you should be supplying a $country code'
            ;
        } else {
            $this->message = 'Language code: "'. $langCode .'" does not exist in the StaticInternational::$languages array.';
        }

    }
}
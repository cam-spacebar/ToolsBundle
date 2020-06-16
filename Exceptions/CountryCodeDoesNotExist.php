<?php
/*
* created on: 09/06/2020 at 3:34 PM
* by: cameronrobertburns
*/

namespace App\VisageFour\Bundle\ToolsBundle\Exceptions;

use VisageFour\Bundle\ToolsBundle\Statics\StaticInternational;

class CountryCodeDoesNotExist extends \Exception
{
    private $countryCode;

    public function __construct($countryCode)
    {
        $this->countryCode = $countryCode;
        parent::__construct();

        $languages = StaticInternational::getLanguages();
        if (!empty($languages[$countryCode])) {
            $this->message =
                'it looks like you have submitted a $language code (code: "'.
                $countryCode .'" for "'. $languages($countryCode) .'") '.
                'when you should be supplying a $country code.'
            ;
            return;
        }

        $this->message = 'Country code: "'. $countryCode .'" does not exist in the StaticInternational::$countries array.';
    }
}
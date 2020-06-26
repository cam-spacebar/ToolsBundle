<?php
/*
* created on: 26/06/2020 at 5:10 PM
* by: cameronrobertburns
*/

namespace App\VisageFour\Bundle\ToolsBundle\Classes;

class DateTimeFormatter
{

    // diff constants
    const DIFF_HIS      = '%H:%I:%S';                                                       // -> 01:25:25
    const DIFF_YMD_HIS  = '%y years %m months %a days %h hours %i minutes %s seconds';      // -> ?
    const DIFF_HI       = '%h hours %i minutes';      // -> 1 hours, 25 minutes

    public static function getTimePassed(\DateTime $earlier, $format = self::DIFF_HIS, \DateTime $later = null ) {
        if (empty($later)) {
            $later = new \DateTime('now');
        }

        $interval = $earlier->diff( $later );

        return $interval->format( $format );
    }
}
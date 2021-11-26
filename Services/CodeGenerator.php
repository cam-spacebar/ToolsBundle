<?php
/*
* created on: 03/06/2020 at 10:12 PM
* by: cameronrobertburns
*/

namespace VisageFour\Bundle\ToolsBundle\Services;

/**
 * Class CodeGenerator
 * @package VisageFour\Bundle\ToolsBundle\Services
 *
 * a class that does the heavy lifting of generating codes
 */
class CodeGenerator
{
    // generate (and return) a code, with x chars first, then y numbers
    public function createRandomCode ($noOfChrs = 3, $noOfNums = 3) {
        $response = '';
        for ($i = 0; $i < $noOfChrs; $i++) {
            $curVal = rand(0, 25);
            $response .= chr (97 + (int) $curVal);
        }

        for ($j = 0; $j < $noOfNums; $j++) {
            $curVal = rand(0, 9);
            $response .= (int) $curVal;
        }

        return $response;
    }

    // generate (and return) a string of x alphanumeric chars
    public function genAlphaNumericCode (int $noOfChars) {
        $ANmapping = $this->getAlphaNumericMapping();
        $newCode = '';

        for ($i=1; $i <= $noOfChars; $i++) {
            $curVal = rand(1, 34);
//            print $curVal .'-';
            $newCode .= $ANmapping[$curVal];
        }
//        dd($newCode);
//        dump(strlen($newCode));
//        dump( is_string($newCode));
//        dd($newCode);

        return $newCode;
    }

    private function getAlphaNumericMapping () {
        if (empty($this->alphaNumbericMapping)) {

            $this->alphaNumbericMapping = array (
                '1'             => 1,
                '2'             => 2,
                '3'             => 3,
                '4'             => 4,
                '5'             => 5,
                '6'             => 6,
                '7'             => 7,
                '8'             => 8,
                '9'             => 9,
                '10'             => 9,
                'a'             => 'a',
                'b'             => 'b',
                '11'             => 'c',
                '12'             => 'd',
                '13'             => 'e',
                '14'             => 'f',
                '15'             => 'g',
                '16'             => 'h',
                '17'             => 'i',
                '18'             => 'j',
                '19'             => 'k',
                '20'             => 'l',
                '21'             => 'm',
                '22'             => 'n',
                '23'             => 'o',
                '24'             => 'p',
                '25'             => 'q',
                '26'             => 'r',
                '27'             => 's',
                '28'             => 't',
                '29'             => 'u',
                '30'             => 'v',
                '31'             => 'w',
                '32'             => 'x',
                '33'             => 'y',
                '34'             => 'z'
            );
        }

        return $this->alphaNumbericMapping;
    }
}
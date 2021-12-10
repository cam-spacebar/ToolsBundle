<?php
/*
* created on: 08/12/2021 - 13:03
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Services\Debugging;

class ConsoleOutput
{
    /**
     * @var TerminalColors
     */
    protected $terminalColors;

    // line break
    private $lb;

    public function __construct()
    {
        $this->terminalColors = new TerminalColors();
        $this->lb = "\n";
    }

    public function outputRedTextToTerminal($msg)
    {
        $output = $this->terminalColors->getColoredString($msg, 'red', 'black');

        print $this->lb . $output;
    }

    public function outputColoredTextToTerminal($msg, $prefixText = '', $fgColor = 'red', $bgColor = 'black')
    {
        $maxPrefixLength = 12;

        $output = $this->terminalColors->getColoredString($msg, $fgColor, $bgColor);
        $prefix = '';
        if (!empty($prefixText)) {
            $prefixText = substr($prefixText, 0, $maxPrefixLength);
            $prefix = $this->terminalColors->getColoredString($prefixText, 'grey_bold', 'black');
        } else {
            $noOfSpaces = 4;
            for ($i=0; $i<$noOfSpaces; $i++) {
                $prefix = $prefix . ' ';
            }
        }
        $prefix = $prefix .': ';


        print $this->lb . $prefix . $output;
    }
}
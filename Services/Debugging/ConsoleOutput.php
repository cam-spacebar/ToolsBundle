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

    public function outputColoredTextToTerminal($msg, $fgColor = 'red', $bgColor = 'black')
    {
        $msg = 'DEBUG: '.$msg;
        $output = $this->terminalColors->getColoredString($msg, $fgColor, $bgColor);

        print $this->lb . $output;
    }
}
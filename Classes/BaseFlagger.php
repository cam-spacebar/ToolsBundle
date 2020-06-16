<?php
/*
* created on: 16/06/2020 at 7:41 PM
* by: cameronrobertburns
*/

namespace VisageFour\Bundle\ToolsBundle\Classes;

/**
 *
 * A flagger that indicates the "status" of a badge or "what part" of the
 * workflow the badge is currently in (curing construction or handout to attendee)
 *
 * This is a “Flagger” CRC (Custom Reusable Component). Read more here: https://bit.ly/30Iq2ip
 *
 * Class BadgeWorkflowFlagger
 * @package App\Classes\Flaggers
 */
abstract class BaseFlagger
{

    /**
     * The class name that the flagger belongs to.
     * @var string
     */
    static protected $className;

    /**
     * name of the flagger
     * @var string
     *
     * the name of your flags, such as: "workflow status" flag or "login form result" flag.
     */
    static protected $name;
    /**
     * the flag value as the key and the "string" version as the value.
     * @var array
     */
    static protected $flagsToText;

    public static function PopulateOptionsAsText()
    {
       throw new \Exception(
           'the BaseFlagger::PopulateOptionsAsText() method has not been overridden (it must return'.
           ' an array with a string of array elements.'
       );
    }

    /**
     * enter in the flag value and return it's string equivalent
     */
    public static function getFlagAsString ($flagValue) {
        self::checkStringVersionAvailable();
        self::checkFlagToTextExists($flagValue);

        return self::$flagsToText[$flagValue];
    }

    private static function getClassNameIsSet () {
        if (empty(self::$className)) {
            throw new \Exception(
                'you must set $className in the populate() method of your '. self::$name .' flagger. Flagger classname: '. __CLASS__
            );
        }
    }

    /**
     * @throws \Exception
     *
     * Check that a text version of the flag is available,
     * if not, throw an exception.
     */
    private static function checkFlagToTextExists (string $flagValue) {
        if (empty(self::$flagsToText[$flagValue])) {
            throw new \Exception(
                self::$name .' flag with value: '. $flagValue .' does not have a string equivalent '.
                 'to translate it into. Check that you have populated the $flagsToTtext array correctly'
            );
        }
    }

    /**
     * Ensures that ::PopulateOptionsAsText() has been overriden and
     * that the $flagsAsText is populated with string versions of each flag.
     *
     * @throws \Exception
     */
    private static function checkStringVersionAvailable() {
        if (empty(self::$flagsToText)) {
            self::PopulateOptionsAsText();
        }
    }
}
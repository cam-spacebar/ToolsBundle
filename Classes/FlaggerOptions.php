<?php
/*
* created on: 23/06/2020 at 10:25 PM
* by: cameronrobertburns
*/

namespace App\VisageFour\Bundle\ToolsBundle\Classes;

/**
 * this is a seperate class from BaseFlagger, so that it enforces use of
 * getFlaggerOptions which calls ensurePopulated() first
 *
 * Class FlaggerOptions
 * @package App\VisageFour\Bundle\ToolsBundle\Classes
 *
 */
abstract class FlaggerOptions
{
    /**
     * the flag value as the key and the "string" version as the value.
     * example array element and index:
     * 175 => 'being constructed (urgently)'
     * or:
     * self:IN_CONSTRUCTION_URGENT => 'being constructed (urgently)'
     * @var array
     */
    static private $flagOptions;

    static private $isPopulated;

    /**
     * name of the flagger
     * @var string
     *
     * the name of your flags, such as: "workflow status" flag or "login form result" flag.
     */
    static private $name;

    /**
     * The class name that the flagger belongs to.
     * (not the actual flaggers classname)
     * @var string
     */
    static protected $className;

    /**
     * Return the $flagsToText array, but ensures that it's populated first!
     * @return array
     */
    protected static function getFlagOptions () {
        self::ensureIsPopulated();

        return self::$flagOptions;
    }

    public static function getFlaggerName () : string
    {
        self::ensureIsPopulated();

        return self::$name;
    }

    public static function getClassName () : string
    {
        self::ensureIsPopulated();

        return self::$className;
    }

    protected static function ensureIsPopulated () {
        if (self::$isPopulated == true) {
            return true;
        } else {
            self::$isPopulated = true;
            static::populate();
        }
    }

    public static function setClassName ($classname) {
        self::$className = $classname;
    }

    public static function setName ($name) {
        self::$name = $name;
    }

    /**
     * add a 'flag option'. Using a method (instead of direct array access) prevents flags
     * being loaded with the same value. This isn't normally a problem until you have
     * flagger classes that inherit from other flagger classes and that build on the flagOptions
     * array in two different places (such as happens with the formFlagger where there's generic flags
     * and then more specific flags.)
     *
     * @param int $flagValue
     * @param string $flagString
     */
    protected static function addFlagOption (int $flagValue, string $flagString) {
        if (!empty(self::$flagOptions[$flagValue])) {
            throw new \Exception (
                'a flag with the value: '. $flagValue .' ("'. static::getFlagAsString($flagValue) .')'.
                ' already exists, the new flag cannot be added.'
            );
        }

        self::$flagOptions[$flagValue] = $flagString;

        return true;
    }
}
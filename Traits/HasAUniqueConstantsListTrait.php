<?php

namespace VisageFour\Bundle\ToolsBundle\Traits;

use VisageFour\Bundle\ToolsBundle\Classes\UniqueConstantsList;

/**
 * Trait HasAUniqueConstantsListTrait
 * @package VisageFour\Bundle\ToolsBundle\Traits
 *
 * This trait provides a shorthand (and abstracted away) set of methods for interacting with the UCL.
 *
 * Why this trait exists:
 * - This exists because there are cases where a class cannot extend UniqueConstantsList (due to single parent inheritance) - this often happens with classes that need to extend \Exception.
 *   So this trait will implement methods to access a UCL in a compositional "has a" (instead of "is a").
 */
trait HasAUniqueConstantsListTrait
{
    /**
     * @var UniqueConstantsList
     */
    protected $uniqueConstantsList;

    public function getListItemByIndex($arrIndex)
    {
        return $this->uniqueConstantsList->getListItemByIndex($arrIndex);
    }

    public function getPayload () {
        return $this->uniqueConstantsList->getPayload();
    }

    public function getValue () {
        return $this->uniqueConstantsList->getValue();
    }

    public function checkConstantIsValid ($arrIndex)
    {
        return $this->uniqueConstantsList->checkConstantIsValid($arrIndex);
    }

    public function setValue($arrIndex)
    {
        $this->uniqueConstantsList->setValue($arrIndex);
    }
}
<?php

namespace VisageFour\Bundle\ToolsBundle\Traits;

use App\VisageFour\Bundle\ToolsBundle\Classes\UniqueConstantsList;

/**
 * Trait HasAUniqueConstantsListTrait
 * @package VisageFour\Bundle\ToolsBundle\Traits
 *
 * This trait provides a shorthand (and abstracted away) set of methods for interacting with the UCL.
 *
 * This exists because there are cases where a class cannot extend UniqueConstantsList (due to single parent inheritance).
 */
trait HasAUniqueConstantsListTrait
{
    protected $uniqueConstantsList;

    public function getListItemByIndexToUCL($arrIndex)
    {
        return $this->uniqueConstantsList->getListItemByIndex($arrIndex);
    }

    protected function addArrayOfItemsToUCL($newArray): self
    {
        return $this->uniqueConstantsList->addArrayOfItems($newArray);
    }

    protected function addListItemToUCL($item, $arrIndex)
    {
        return $this->uniqueConstantsList->addListItem($item, $arrIndex);
    }
}
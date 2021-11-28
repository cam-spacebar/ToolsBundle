<?php

namespace VisageFour\Bundle\ToolsBundle\Classes;

use VisageFour\Bundle\ToolsBundle\Interfaces\UniqueConstantsListInterface;

/**
 * Class UniqueConstantsList
 * @package App\Classes
 *
 * Unique constants is designed to manage the creation of a list of 'unique class constants' - and their "payload value".
 *
 * Purpose:
 * - Prevent undetected overwriting of list items!
 * - detect accidental pass in of null list/array indexes (on get method calls).
 * - clearer exceptions when attempting to access a non-existent list index
 * - abstract away logic from main classes
 * - centralize re-usable code / "write once"
 *
 * Todo:
 * - Create a CRC to document this design.
 * - apply this design to: BaseFlagger
 * - apply this design to: BaseFrontendUrl
 */
class UniqueConstantsList implements UniqueConstantsListInterface
{
    /**
     * @var string
     * examples: "route-pair", "flag", "ErrorCode"
     * the term used to describe each item in the list.
     */
    private $itemPhrase;

    /**
     * @var
     * The value of "this object" - corresponding to the array index (within the ->list) / AKA the "constant-value".
     */
    private $value;

    /**
     * @var string
     *
     * This is a code marker that is displayed to the developer  so they
     * can quickly/easily add a new item to the UCL
     */
    private $addItemMarker;

    /**
     * @var array
     */
    private $list;

    public function __construct(string $itemPhrase, array $listItemsToMerge, $initialValue, string $addItemMarker = null)
    {
        $this->itemPhrase = $itemPhrase;
        $this->addItemMarker = $addItemMarker;
        $this->mergeItemArrays($listItemsToMerge);
        $this->setValue($initialValue);
    }

    public function setValue($value)
    {
        $this->checkConstantIsValid($value);
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    /**
     * check that the value provided matches an option provided in the list.
     */
    public function checkConstantIsValid($constantValue)
    {
        if (array_key_exists($constantValue, $this->list)) {
            return true;
        }

        $phrase = $this->itemPhrase;
        throw new \Exception(
            $phrase .' with value: "'. $constantValue .'" does not exist in the '. $phrase .'s list. '.
            'Please review the '. $phrase .' provided or add the new code to the possible options.'
        );
    }

    public function getListItemByIndex($arrIndex)
    {
        $this->checkIndexExists($arrIndex);

        return $this->list[$arrIndex];
    }

    /**
     * return the value (aka "payload") of the currently set "value":
     */
    public function getPayload()
    {
        return $this->getListItemByIndex($this->value);
    }

    /**
     * @param array $arraysToMerge
     *
     * due to the fact that additional-items are added to the list via class extension,
     * there must be a way for the UCL to safely merge together multiple arrays of list items.
     * That's what this method does.
     */
    private function mergeItemArrays(array $arraysToMerge)
    {
        foreach ($arraysToMerge as $curI => $curList) {
            $this->addArrayOfItems($curList);
        }
    }

    /**
     * @param $routes
     * @return $this
     *
     * loop over the supplied array and add it's items to this objects unique items list.
     */
    private function addArrayOfItems($newArray): UniqueConstantsListInterface
    {
        if (empty($newArray)){
            return $this;
        }

        foreach ($newArray as $curI => $curItem) {
            $this->addListItem($curItem, $curI);
        }

        return $this;
    }

    private function addListItem($item, $arrIndex)
    {
        $this->checkIndexIsNotAlreadySet($arrIndex);

        $this->list[$arrIndex] = $item;
    }

    private function checkIndexIsNotAlreadySet($arrIndex)
    {
        $addItemsMarker = $this->getAddItemsMarkerAsString();
        if (isset($this->list[$arrIndex])) {
            throw new \Exception(
                'Cannot add a item to the "'. $this->itemPhrase .'" Unique Constants List (UCL) (with index: '. $arrIndex .'),'.
            ' as this array index has already been used. Please update the item index to be unique (see marker: '. $this->addItemMarker .').'
                .$addItemsMarker
            );
        }
    }

    /**
     * return a string that can be used in an exception message
     */
    private function getAddItemsMarkerAsString()
    {
        if (!empty($this->addItemMarker)) {
            return ' (You can add new '. $this->itemPhrase .'s by searching for the marker: #'. $this->addItemMarker .' in your IDE.)';
        }

        return '';
    }

    private function checkIndexExists($arrIndex)
    {
        if (!isset($this->list[$arrIndex])) {
            throw new \Exception(
                'Cannot retrieve list item with the array index: '. $arrIndex .' (on the '. $this->itemPhrase .' list as it) does not exist.'
            );
        }

        return true;
    }
}
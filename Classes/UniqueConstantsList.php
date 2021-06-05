<?php

namespace App\VisageFour\Bundle\ToolsBundle\Classes;

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
 * todo:
 * - Create a CRC to document this design.
 * - apply this design to: BaseFlagger
 * - apply this design to: BaseFrontendUrl
 */
abstract class UniqueConstantsList
{
    /**
     * @var string
     * examples: "route-pair" list, "flagger options" list - (note: don't include the string: ' list' in the phrase, this is autmoatically added)
     * the "name" of the list
     */
    private $listPhrase;

    /**
     * @var string
     * examples: "route-pair", "flag", "ErrorCode"
     * the term used to describe each item in the list.
     */
    private $itemPhrase;

    public function __construct($listPhrase, $itemPhrase)
    {
        $this->listPhrase = $listPhrase;
        $this->itemPhrase = $itemPhrase;
    }

    /**
     * @var array
     */
    private $list;

    /**
     * @param $routes
     * @return $this
     *
     * loop over the supplied array and add it's items to this objects unique items list.
     */
    protected function addArrayOfItems($newArray): self
    {
        foreach ($newArray as $curI => $curItem) {
            $this->addListItem($curItem, $curI);
        }

        return $this;
    }

    protected function addListItem($item, $arrIndex)
    {
        $this->checkIndexIsNotAlreadySet($arrIndex);

        $this->list[$arrIndex] = $item;
    }

    protected function getListItemByIndex($arrIndex)
    {
        $this->checkIndexExists($arrIndex);

        return $this->list[$arrIndex];
    }

    private function checkIndexIsNotAlreadySet($arrIndex)
    {
        if (isset($this->stdResponses[$arrIndex])) {
            throw new \Exception('Cannot add an item (with index: '. $arrIndex .') to the '. $this->listPhrase .' list, as this index has already been set.');
        }
    }

    private function checkIndexExists($arrIndex)
    {
        if (!isset($this->list[$arrIndex])) {
            throw new \Exception('Cannot retrieve list item with the index: '. $arrIndex .' (on the '. $this->listPhrase .' list as it) does not exist.');
        }

        return true;
    }
}
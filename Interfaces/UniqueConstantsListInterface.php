<?php

namespace VisageFour\Bundle\ToolsBundle\Interfaces;

interface UniqueConstantsListInterface {
    function setValue($value);
    function getValue();
    function getPayload();

    function checkConstantIsValid($constantValue);
    function getListItemByIndex($arrIndex);

}
?>
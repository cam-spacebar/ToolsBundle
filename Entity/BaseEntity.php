<?php

namespace VisageFour\Bundle\ToolsBundle\Entity;

use VisageFour\Bundle\ToolsBundle\Interfaces\BaseEntityInterface;

/**
 *
 */
abstract class BaseEntity implements BaseEntityInterface
{
    // this is part of a Custom Reusable Component (CRC), you can learn
    // more about is via it’s CRC readme here: https://bit.ly/2XIrgab
    public function getLoggingData (int $detailLevel) : array {

        return array (
            'id'                        => $this->id,
            // this element should be wiped out when overriding this class.
            /// it's used to detect if the method has bee overridden or not.
            'methodNotImplemented'      => true,
        );
    }

    const LOG_DETAIL_NONE       = 0;
    const LOG_DETAIL_BASIC      = 1;
    const LOG_DETAIL_MORE       = 2;
}
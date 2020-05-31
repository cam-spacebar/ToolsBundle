<?php
/*
* created on: 30/05/2020 at 3:55 PM
* by: cameronrobertburns
*/

namespace VisageFour\Bundle\ToolsBundle\Services;

use VisageFour\Bundle\ToolsBundle\Interfaces\BaseEntityInterface;
use Psr\Log\LoggerInterface;
use VisageFour\Bundle\ToolsBundle\Entity\BaseEntity;

/**
 * Class LoggingExtraData
 * @package App\VisageFour\Bundle\ToolsBundle\Classes
 *
 * [Overview:] get the logging data from a $obj, complete checks
 * normalize logging data and return it to the client. Ussually
 * for the purpose of creating a log record about an entity.
 *
 * [CRC readme:] This code is part of a Custom Reusable Component (CRC),
 * you can learn more about is via it’s CRC readme here:
 * https://bit.ly/2XIrgab
 */
class LoggingExtraData
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    private $classname;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger   = $logger;
    }

    /**
     * returns an array of string elements that represents (some of) the $obj
     */
    public function getObjLoggingData(BaseEntityInterface $obj, $detailLevel = BaseEntity::LOG_DETAIL_BASIC)
    {
        $arr = $obj->getLoggingData($detailLevel);

        if (empty($arr)) {
            $warn =
                'getLoggingdata() for class: '. $this->classname .' must be populated';
            ;
        }

        // check if the method has been overridden:
        if (!empty($arr['methodNotImplemented'])) {
            $warn =
                'class with FQCN: '. $this->classname .' likely hasn\'t implemented the getLoggingData() method.'.
                'Please implement it, see: BaseEntity::getLoggingData() for documentation.'
            ;
        }

        unset($arr['methodNotImplemented']);

        $arr = $this->normalizeLoggingData ($arr);

        if (!empty($warn)) {
            $this->logger->warning($warn);
        }
    }

    public function checkClassHasLoggingDataMethod (BaseEntityInterface $obj) {
        $this->classname = get_class($obj);
        // check entity compatibility
        if (!method_exists($obj, 'getLoggingData')) {
            throw new \Exception(
                'entity with classname: '. $this->classname.
                ' does not extend the BaseEntity class and implement method: loggingData().'.
                ' Please extend your entity class and implement this method.'
            );
        }
    }

    /**
     * @param array $arr
     *
     * Converts array elements (that are not strings) into strings
     * so that they can be printed in the log.
     *
     * todo: it should be able to handle date objects
     */
    private function normalizeLoggingData (array $arr) {
        foreach ($arr as $curI => $curVal) {
            if (is_string($curVal)) { continue; }

            if ($curVal == true) {
                $arr[$curI] = 'true';
                continue;
            }

            if ($curVal == false) {
                $arr[$curI] = 'false';
                continue;
            }

            if ($curVal == null) {
                $arr[$curI] = 'null';
                continue;
            }

            $errMsg =
                'Could not convert member variable: "'. $curI .' (of class: "'. $this->classname .'") into a string. '.
                'It\'s type is: "'. $curVal .'". '.
                'You must normalize it in your entity\'s getLoggingData() method.'
            ;
            throw new \Exception ($errMsg);
        }

        return $arr;
    }




}
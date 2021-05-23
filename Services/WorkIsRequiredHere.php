<?php

namespace App\VisageFour\Bundle\ToolsBundle\Services;

use Twencha\Bundle\EventRegistrationBundle\Classes\AppSettings;

/**
 * Class WorkIsRequiredHere
 * @package App\VisageFour\Bundle\ToolsBundle\Services
 *
 * a class that's used to indicate where work needs to be done (remove old legacy code or rewrite legacy code).
 *
 */
class WorkIsRequiredHere
{

    public function __construct()
    {

    }

    /**
     * @param $markingDate
     *
     * This will throw an exception during dev (if the code is used), but in prod, it will run.
     *
     * It throws because it's assumed the code is no longer used - this helps bring the pathway to use to the attention of the developer.
     *
     * use the date to indicate when the code was marked for deletion
     */
    static public function markedForDeletion($markingDate)
    {
        if(AppSettings::$kernelEnv == 'dev') {
            throw new \Exception(
                'This code was marked for deletion on: '. $markingDate .'.
                If you are seeing this, then the code is still in use somewhere and it is NOT safe to delete - and this notice should be removed.'
            );
        }

        // if in 'prod':
        return true;
    }

    static public function needsRewriting()
    {
        if(AppSettings::$kernelEnv == 'dev') {
            throw new \Exception(
                'this code has been marked as needing to be rewritten'
            );
        }
    }
}
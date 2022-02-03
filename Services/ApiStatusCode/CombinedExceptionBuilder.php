<?php
/*
* created on: 03/02/2022 - 14:45
* by: Cameron
*/


namespace VisageFour\Bundle\ToolsBundle\Services\ApiStatusCode;

use VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode\CombinedException;
use VisageFour\Bundle\ToolsBundle\Interfaces\ApiErrorCodeInterface;
use VisageFour\Bundle\ToolsBundle\Services\ApiStatusCode\LogException;

/**
 * Class CombinedExceptionsBuilder
 * @package App\VisageFour\Bundle\ToolsBundle\Services\ApiStatusCode
 *
 * ogs and stores exceptions and then throw the CombinedException once all exceptions have been added (by calling: throwIfHasErrors())
 */
class CombinedExceptionBuilder
{
    /**
     * @var LogException
     */
    private $logException;

    private $exceptionList;

    public function __construct(LogException $logException)
    {

        $this->logException = $logException;
    }

    /**
     * log the exception (via Logexception service) and add rhe exception to the array of responses.
     * The $reference is what allows the front-end to match the error message to right UI component.
     */
    public function addException(ApiErrorCodeInterface $e, string $reference, $logException = true)
    {
        if ($logException) {
            $this->logException->run($e);
        }

        if (isset($this->exceptionList[$reference])) {
            throw new \Exception('the error $reference: '. $reference .' has already been populated, cannot add to it again.');
        }

        $this->exceptionList[$reference] = $e;

        return true;
    }

    /**
     * throw a the combined exception if: there was any exceptions added. Otherwise do nothing.
     * (note: this is ussually called at the end of a loop where exceptions may be added.)
     */
    public function throwIfHasErrors()
    {
        if (!empty($this->exceptionList)) {
            $combinedException = new CombinedException();

            foreach($this->exceptionList as $reference => $curException) {
                $combinedException->addPublicErrorMessage($curException, $reference);
            }

            throw $combinedException;
        }

        return true;
    }
}
<?php
/*
* created on: 02/02/2022 - 19:55
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode;

use App\Exceptions\ApiErrorCode;
use VisageFour\Bundle\ToolsBundle\Interfaces\ApiErrorCodeInterface;
use VisageFour\Bundle\ToolsBundle\Services\ApiStatusCode\LogException;

/**
 * Class ResponseCombiner
 * @package App\VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode
 *
 * --- Documentation ---
 * https://docs.google.com/presentation/d/1WpWj80uAeQLJtNabbn-CxppNU-pEqteE5g222_qmjCo/edit#slide=id.g11257e5caa1_0_0
 *
 */
class CombinedException extends ApiErrorCode
{
    private $combinedPublicErrorMessages;

    public function __construct (
        $clientMsg = 'Please see the exceptionResponses for all error messages (admin only).'
    ) {
//        find out how to add array to response - clientMsg should be a string?
        parent::__construct(
            ApiErrorCode::MIXED_RESPONSES,
            $clientMsg,
            $clientMsg
        );
    }

    public function addPublicErrorMessage(ApiErrorCodeInterface $e, string $reference)
    {
        $this->combinedPublicErrorMessages[$reference] = $e->getPublicMsg();
    }

    // Return an array that contains the error messages (and their "references") from all exceptions "combined" that were added
    public function getCombinedPublicErrorMessages()
    {
//        dd($this->combinedPublicErrorMessages, '123dcsdf');
        return $this->combinedPublicErrorMessages;
    }
}
<?php
/*
* created on: 02/12/2021 - 20:32
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Services\QRcode;

use Doctrine\ORM\EntityManager;
use VisageFour\Bundle\ToolsBundle\Services\QRcode\QRCodeGenerator;
use VisageFour\Bundle\ToolsBundle\Traits\LoggerTrait;

/**
 * Class TrackedAssetGenerator
 * @package App\VisageFour\Bundle\ToolsBundle\Services\QRcode
 *
 * it produces images / PDFs that have QRcodes overlayed
 */
class TrackedAssetGenerator
{
    use LoggerTrait;

    /** @var EntityManager */
    private $em;

    /**
     * @var QRCodeGenerator
     */
    private $QRCodeGenerator;

    public function __construct(EntityManager $em, QRCodeGenerator $QRCodeGenerator)
    {
        $this->em                   = $em;
        $this->QRCodeGenerator = $QRCodeGenerator;
    }

}
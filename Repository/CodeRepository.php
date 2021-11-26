<?php

namespace VisageFour\Bundle\ToolsBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use VisageFour\Bundle\ToolsBundle\Entity\Code;
use VisageFour\Bundle\ToolsBundle\Repository\NoAutowire\BaseRepository;
use VisageFour\Bundle\ToolsBundle\Services\CodeGenerator;

/**
 * CodeRepository
 */
class CodeRepository extends BaseRepository
{
    /**
     * @var CodeGenerator
     */
    private $codeGenerator;

    public function __construct (ManagerRegistry $registry, string $class, CodeGenerator $codeGen) {
        parent::__construct($registry, $class);

        $this->codeGenerator    = $codeGen;
    }

    public function getByCode($code) : Code
    {
        return $this->findOneBy([
            'code' => $code
        ]);
    }

    // generate a unique (not used before) code
    protected function createNewUniqueCode($noOfCharsInCode, $loopNo = 0)
    {
        $newCode = $this->codeGenerator->genAlphaNumericCode($noOfCharsInCode);

        // check if the code is unique or not
        $result = $this->findOneBy([
            'code'      => $newCode
        ]);

        if (!empty($result)) {
            // loop until we find a new unique code
            $loopNo = $loopNo +1;
            if ($loopNo > 5000) {
                throw new \Exception('unable to find a unique code even after '. $loopNo .' tries');
            }
            return $this->createNewUniqueCode($noOfCharsInCode, $loopNo);
        }

        return $newCode;
    }
}

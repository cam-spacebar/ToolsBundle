<?php

namespace VisageFour\Bundle\ToolsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\MappedSuperclass
 */
class Code extends BaseEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=32, unique=true, nullable=true)
     *
     * a randomly generated, unique code - that can be used in different ways
     * by the child class.
     */
    protected $code;
    static public $codeNoOfChars = 32;

    // code generation strategy
    const CODE_GEN_STRAT_BASIC              = 100;      // e.g. ecz348
    const CODE_GEN_STRAT_RAND_ALPHA_NUMBERIC           = 200;      // md5 hashes a randomly generated string.
//    const CODE_GEN_STRAT_20_alpha_numberic  = 300;      // 20 characters alpha-numberic

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return code
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
}

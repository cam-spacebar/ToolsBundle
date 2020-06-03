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
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="createdAt", type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updatedAt", type="datetime")
     */
    protected $updatedAt;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=32, unique=false)
     */
    protected $code;

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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return code
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return code
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
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

    // see BaseEntity (base) class for more information on this method.
    public function getLoggingData($detailLevel = BaseEntity::LOG_DETAIL_BASIC) : array
    {
        if ($detailLevel >= BaseEntity::LOG_DETAIL_BASIC) {
            $arr = [
                'id'                => $this->id,
                'code'              => $this->code
            ];
        }

        if ($detailLevel >= BaseEntity::LOG_DETAIL_MORE) {
//            $arr ['??'] = ";"
        }

        return $arr;
    }
}

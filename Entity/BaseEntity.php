<?php

namespace VisageFour\Bundle\ToolsBundle\Entity;

use VisageFour\Bundle\ToolsBundle\Interfaces\BaseEntityInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 *
 */
abstract class BaseEntity implements BaseEntityInterface
{
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

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return BaseEntityInterface
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
     * @return BaseEntityInterface
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

    const LOG_DETAIL_NONE       = 0;
    const LOG_DETAIL_BASIC      = 1;
    const LOG_DETAIL_MORE       = 2;
}
<?php

namespace VisageFour\Bundle\ToolsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

// do not include ORM mapping here as the sub-classed slug cannot inherit the OneToOne relationship
// some reason, the ORM doesn't allow this to be a mapped superclass if itself is an entity.
// plus I don't need an extra table in the DB that an super-entity would have created.
class Slug
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
     * @ORM\Column(name="CreatedAt", type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="UpdatedAt", type="datetime")
     */
    protected $updatedAt;

    /**
     * @var Code
     *
     * @ORM\OneToOne(targetEntity="Twencha\Bundle\EventRegistrationBundle\Entity\Code", cascade={"persist"})
     * @ORM\JoinColumn(name="related_code_id", referencedColumnName="id")
    */
    protected $relatedCode;

    // START: GETTERS AND SETTERS
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
     * @return Slug
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
     * @return Slug
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

    public function __construct()
    {
        //$this->relatedRegistrationList = new ArrayCollection();
    }

    /**
     * @return Code
     */
    public function getRelatedCode()
    {
        return $this->relatedCode;
    }

    /**
     * @param Code $relatedCode
     */
    public function setRelatedCode(Code $relatedCode)
    {
        $this->relatedCode = $relatedCode;

        return $this;
    }
}
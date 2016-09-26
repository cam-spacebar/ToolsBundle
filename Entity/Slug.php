<?php

namespace VisageFour\Bundle\ToolsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Slug
 *
 * @ORM\Table(name="slug")
 * @ORM\Entity(repositoryClass="VisageFour\Bundle\ToolsBundle\Repository\SlugRepository")
 */
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
     * @ORM\ManyToOne(targetEntity="VisageFour\ToolsBundle\Entity\Code")
     * @ORM\JoinColumn(name="related_code_id", referencedColumnName="id")
     */
    private $relatedCode;

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
     * @return mixed
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
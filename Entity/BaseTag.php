<?php
/*
* created on: 04/11/2021 - 23:00
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Entity;

use App\Entity\Purchase\AttributionTag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping as ORM;
use VisageFour\Bundle\ToolsBundle\Entity\BaseEntity;
use VisageFour\Bundle\ToolsBundle\Interfaces\TagInterface;

/**
 * @MappedSuperClass
 *
 * Class BaseTag
 * @package App\VisageFour\Bundle\ToolsBundle\Entity
 *
 * A generic class that allows you to add "tags" to objects
 * these tags may be things like: categories, marketing channels etc.
 *
 * the Parent property allows the creation or limitless hierarchies.
 */
class BaseTag extends BaseEntity
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Purchase\AttributionTag", inversedBy="relatedChildTags")
     * @ORM\JoinColumn(name="related_parent_tag_id", referencedColumnName="id", nullable=true)
     *
     * @var $relatedParentTag AttributionTag
     */
    protected $relatedParentTag;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Purchase\AttributionTag", mappedBy="relatedParentTag")
     *
     */
    protected $relatedChildTags;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     *
     * Its name / label
     */
    protected $name;

    /**
     * BaseTag constructor.
     */
    public function __construct($name, $parent = null)
    {
        $this->name = $name;
        $this->relatedChildTags = new ArrayCollection();

        if (!empty($parent)) {
            $this->relatedParentTag = $parent;
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return ArrayCollection
     */
    public function getRelatedChildTags(): ArrayCollection
    {
        return $this->relatedChildTags;
    }

    /**
     * @param TagInterface $tag
     * @param bool $addToOppositeSide
     * @return bool
     */
    public function addRelatedChildTag(TagInterface $tag, $addToOppositeSide = true): bool
    {
        if ($this->relatedChildTags->contains($tag)) {
            return true;
        }

        $this->relatedChildTags->add($tag);
        if ($addToOppositeSide) {
            $tag->setRelatedParentTag($this);
        }

        return true;
    }

    /**
     * @return TagInterface
     */
    public function getRelatedParentTag(): ?TagInterface
    {
        return $this->relatedParentTag;
    }

    public function setRelatedParentTag(?TagInterface $parentTag, $addToRelation = true): void
    {
        if ($addToRelation) {
            if (!empty($parentTag)) {
                $parentTag->addRelatedChildTag($this);
            }
        }

        $this->relatedParentTag = $parentTag;
    }

    // used with BaseEntity->outputContents() (for console or testing)
    public function getOutputContents()
    {
        return [
            'name'      => $this->name,
            'parent'    => $this->getParentName()
        ];
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getParentName()
    {
        if (!empty($this->relatedParentTag)) {
            return $this->relatedParentTag->getName();
        }

        return null;
    }
}
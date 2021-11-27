<?php
/*
* created on: 25/11/2021 - 12:32
* by: Cameron
*/


namespace VisageFour\Bundle\ToolsBundle\Entity\FileManager;

use App\Entity\FileManager\ImageOverlay;
use Doctrine\ORM\Mapping\MappedSuperclass;
use VisageFour\Bundle\ToolsBundle\Entity\BaseEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\FileManager\File;

/**
 * Class BaseTemplate
 * @package VisageFour\Bundle\ToolsBundle\Entity\FileManager
 *
 * @MappedSuperclass
 */
class BaseTemplate extends BaseEntity
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity=File::class, inversedBy="relatedTemplates")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $relatedOriginalFile;

    /**
     * @ORM\OneToMany(targetEntity=File::class, mappedBy="relatedTemplate")
     */
    protected $relatedDerivativeFiles;

    /**
     * @ORM\OneToMany(targetEntity=ImageOverlay::class, mappedBy="relatedTemplate", orphanRemoval=true)
     */
    protected $relatedImageOverlays;

    public function __construct()
    {
        $this->relatedDerivativeFiles   = new ArrayCollection();
        $this->relatedImageOverlays     = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRelatedOriginalFile(): ?File
    {
        return $this->relatedOriginalFile;
    }

    public function setRelatedOriginalFile(?File $relatedOriginalFile): self
    {
        $this->relatedOriginalFile = $relatedOriginalFile;

        return $this;
    }

    /**
     * @return Collection|File[]
     */
    public function getRelatedDerivativeFiles(): Collection
    {
        return $this->relatedDerivativeFiles;
    }

    public function addRelatedDerivativeFile(File $relatedDerivativeFile): self
    {
        if (!$this->relatedDerivativeFiles->contains($relatedDerivativeFile)) {
            $this->relatedDerivativeFiles[] = $relatedDerivativeFile;
            $relatedDerivativeFile->setRelatedTemplate($this);
        }

        return $this;
    }

    public function removeRelatedDerivativeFile(File $relatedDerivativeFile): self
    {
        if ($this->relatedDerivativeFiles->removeElement($relatedDerivativeFile)) {
            // set the owning side to null (unless already changed)
            if ($relatedDerivativeFile->getRelatedTemplate() === $this) {
                $relatedDerivativeFile->setRelatedTemplate(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection|ImageOverlay[]
     */
    public function getRelatedImageOverlays(): Collection
    {
        return $this->relatedImageOverlays;
    }

    public function addRelatedImageOverlay(ImageOverlay $relatedImageOverlay): self
    {
        if (!$this->relatedImageOverlays->contains($relatedImageOverlay)) {
            $this->relatedImageOverlays[] = $relatedImageOverlay;
            $relatedImageOverlay->setRelatedTemplate($this);
        }

        return $this;
    }

    public function removeRelatedImageOverlay(ImageOverlay $relatedImageOverlay): self
    {
        if ($this->relatedImageOverlays->removeElement($relatedImageOverlay)) {
            // set the owning side to null (unless already changed)
            if ($relatedImageOverlay->getRelatedTemplate() === $this) {
                $relatedImageOverlay->setRelatedTemplate(null);
            }
        }

        return $this;
    }
}
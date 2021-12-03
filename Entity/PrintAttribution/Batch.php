<?php

namespace VisageFour\Bundle\ToolsBundle\Entity\PrintAttribution;

use App\Entity\FileManager\Template;
use VisageFour\Bundle\ToolsBundle\RepositoryAutowired\PrintAttribution\BatchRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use VisageFour\Bundle\ToolsBundle\Entity\PrintAttribution\TrackedFile;

/**
 * @ORM\Entity(repositoryClass=BatchRepository::class)
 */
class Batch
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity=TrackedFile::class, mappedBy="relatedTrackedFile")
     */
    private $TrackedFile;

    /**
     * @ORM\ManyToOne(targetEntity=Template::class, inversedBy="relatedBatches")
     * @ORM\JoinColumn(nullable=false)
     */
    private $relatedTemplate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $payload;

    public function __construct()
    {
        $this->TrackedFile = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|TrackedFile[]
     */
    public function getTrackedFile(): Collection
    {
        return $this->TrackedFile;
    }

    public function addTrackedFile(TrackedFile $trackedFile): self
    {
        if (!$this->TrackedFile->contains($trackedFile)) {
            $this->TrackedFile[] = $trackedFile;
            $trackedFile->setRelatedTrackedFile($this);
        }

        return $this;
    }

    public function removeTrackedFile(TrackedFile $trackedFile): self
    {
        if ($this->TrackedFile->removeElement($trackedFile)) {
            // set the owning side to null (unless already changed)
            if ($trackedFile->getRelatedTrackedFile() === $this) {
                $trackedFile->setRelatedTrackedFile(null);
            }
        }

        return $this;
    }

    public function getRelatedTemplate(): ?Template
    {
        return $this->relatedTemplate;
    }

    public function setRelatedTemplate(?Template $relatedTemplate): self
    {
        $this->relatedTemplate = $relatedTemplate;

        return $this;
    }

    public function getPayload(): ?string
    {
        return $this->payload;
    }

    public function setPayload(?string $payload): self
    {
        $this->payload = $payload;

        return $this;
    }
}

<?php

namespace VisageFour\Bundle\ToolsBundle\Entity\PrintAttribution;

use App\Entity\FileManager\Template;
use VisageFour\Bundle\ToolsBundle\Entity\BaseEntity;
use VisageFour\Bundle\ToolsBundle\RepositoryAutowired\PrintAttribution\BatchRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use VisageFour\Bundle\ToolsBundle\Entity\PrintAttribution\TrackedFile;

/**
 * @ORM\Entity(repositoryClass=BatchRepository::class)
 */
class Batch extends BaseEntity
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
    private $trackedFiles;

    /**
     * @ORM\ManyToOne(targetEntity=Template::class, inversedBy="relatedBatches")
     * @ORM\JoinColumn(nullable=false)
     *
     * The template that will be applied to generate each of the composites
     */
    private $relatedTemplate;

    /**
     * @ORM\Column(type="text", nullable=true)
     * a (serialized) associative array that contains the values that are then used as the value for QR codes that are overlayed onto a canvas image / PDF
     * the array keys represents match the ImageOverlay->labelName property and the array (value) is then used in the QR code
     * (e.g. array key: 'url', will have array contents of e.g.: "www.NewToMelbourne.org/xyz" - which is used in the QR code.)
     */
    private $payload;

    public function __construct(Template $template, array $payload)
    {
        $this->trackedFiles = new ArrayCollection();
        $this->relatedTemplate = $template;
        $this->setPayload($payload);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|TrackedFile[]
     */
    public function getTrackedFiles(): Collection
    {
        return $this->trackedFiles;
    }

    public function addTrackedFile(TrackedFile $trackedFile): self
    {
        if (!$this->trackedFiles->contains($trackedFile)) {
            $this->trackedFiles[] = $trackedFile;
            $trackedFile->setRelatedBatch($this);
        }

        return $this;
    }

    public function removeTrackedFile(TrackedFile $trackedFile): self
    {
        if ($this->trackedFiles->removeElement($trackedFile)) {
            // set the owning side to null (unless already changed)
            if ($trackedFile->getRelatedBatch() === $this) {
                $trackedFile->setRelatedBatch(null);
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

    public function getPayload(): ?array
    {
        return unserialize($this->payload);
    }

    public function setPayload(array $payload): self
    {
        if (!is_array($payload)) {
            throw new \Exception('$payload must be an array');
        }

        $this->payload = serialize($payload);

        return $this;
    }
}

<?php
/*
* created on: 01/12/2021 - 12:11
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Entity\PrintAttribution;

use App\Entity\FileManager\File;
use App\Entity\UrlShortener\Url;
use VisageFour\Bundle\ToolsBundle\Entity\PrintAttribution\Batch;
use VisageFour\Bundle\ToolsBundle\RepositoryAutowired\PrintAttribution\TrackedFileRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use VisageFour\Bundle\ToolsBundle\Entity\BaseEntity;

/**
 * @ORM\Entity(repositoryClass=TrackedFileRepository::class)
 * @ORM\Table(name="boomerprint_trackedfile")
 *
 * This ties a File and Url/s entities together. Practically speaking it was designed to track image files (posters or flyers) that have QR codes that contain shortened URLs
 */
class TrackedFile extends BaseEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity=Url::class, mappedBy="trackedFiles")
     */
    private $relatedUrls;

    /**
     * @ORM\OneToOne(targetEntity=File::class, inversedBy="relatedTrackedFile", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $relatedFile;

    /**
     * @ORM\Column(type="integer")
     *
     * Order the trackedFile has in the $relatedBatch
     */
    private $order;

    /**
     * @ORM\ManyToOne(targetEntity=Batch::class, inversedBy="TrackedFile")
     * @var Batch
     */
    private $relatedBatch;

    public function __construct()
    {
        $this->relatedUrls = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRelatedUrls(): ArrayCollection
    {
        return $this->relatedUrls;
    }

    public function addRelatedUrl(Url $url): self
    {
        if (!$this->relatedUrls->contains($url)) {
            $this->relatedUrls[] = $url;
            $url->setTrackedFile($this);
        }

        return $this;
    }

    public function removeRelatedUrl(Url $url): self
    {
        if ($this->relatedUrls->removeElement($url)) {
            // set the owning side to null (unless already changed)
            if ($url->getRelatedUrls() === $this) {
                $url->setRelatedUrls(null);
            }
        }

        return $this;
    }

    public function getRelatedFile(): ?File
    {
        return $this->relatedFile;
    }

    public function setRelatedFile(File $relatedFile): self
    {
        $this->relatedFile = $relatedFile;

        return $this;
    }

    /**
     * @param int $id
    */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return Int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Int $order
     */
    public function setOrder($order): Int
    {
        $this->order = $order;
    }

    /**
     * @return Batch
     */
    public function getRelatedBatch(): Batch
    {
        return $this->relatedBatch;
    }

    /**
     * @param Batch $relatedBatch
     */
    public function setRelatedBatch(Batch $relatedBatch): void
    {
        $this->relatedBatch = $relatedBatch;
    }
}

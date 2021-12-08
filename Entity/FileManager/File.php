<?php
/*
* created on: 21/11/2021 - 13:24
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Entity\FileManager;

use App\Entity\FileManager\Template;
use App\Entity\Person;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use VisageFour\Bundle\ToolsBundle\Classes\ImageOverlay\Image;
use VisageFour\Bundle\ToolsBundle\Entity\BaseEntity;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\Common\Collections\Collection;
use VisageFour\Bundle\ToolsBundle\Interfaces\FileManager\FileInterface;
use VisageFour\Bundle\ToolsBundle\Interfaces\PrintAttribution\TemplateInterface;

/**
 * @MappedSuperclass
 * Class File
 * @package App\VisageFour\Bundle\ToolsBundle\Entity\FileManager
 *
 * this entity stores details about a file that's created/uploaded. important details like:
 * owner person, file size, original name and even alows for things like version history of a file and duplication detection (via checksum hash)
 */
class File extends BaseEntity implements FileInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="remoteFilePath", type="string", length=255)
     *
     * The remoteFilePath, wherever it's stored (e.g. AWS S3)
     */
    private $remoteFilePath;

    /**
     * @var string
     *
     * @ORM\Column(name="localFilePath", type="string", length=255, nullable=true)
     *
     * the local filepath of the file. This is set when creating the file (from the original) or when downloading from the remote - as a temporary file.
     * (note: the $localFilePath should be treated as unreliable - as the hosting is ephemeral (so: always check the file exists before using it).
     */
    private $localFilePath;

    /**
     * @var string
     *
     * @ORM\Column(name="originalBasename", type="string", length=255, nullable=false)
     */
    private $originalBasename;

    /**
     * @var string
     *
     * @ORM\Column(name="fileExtension", type="string", length=10, nullable=true)
     * the *actual* file's extension (not originalBasename)
     */
    private $fileExtension;

    /**
     * @var integer
     *
     * @ORM\Column(name="filesize", type="integer", nullable=true)
     *
     * Filesize in (bytes)
     */
    private $filesize;

    /**
     * @var string
     *
     * @ORM\Column(name="contentsCheckSum", type="string", length=32, nullable=true)
     *
     * the MD5 cryptographic hash of the file contents (designed to help determine if uploaded files are duplicates).
     */
    private $contentsCheckSum;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="blob", nullable=true)
     */
    private $description;

    /**
     * @var Person
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Person", inversedBy="Person")
     * @ORM\JoinColumn(name="ownerPerson_id", referencedColumnName="id")
     */
    private $relatedOwnerPerson;

    /**
     * @var boolean
     *
     * @ORM\Column(name="flaggedForDelete", type="boolean", nullable=false)
     */
    private $flaggedForDelete;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isDeleted", type="boolean", nullable=false)
     */
    private $isDeleted;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deletionDateTime", type="datetime", nullable=true)
     */
    private $deletionDateTime;

    /**
     * @ORM\OneToMany(targetEntity=Template::class, mappedBy="relatedOriginalFile")
     */
    protected $relatedTemplates;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\FileManager\File", inversedBy="relatedDerivativeFiles")
     * @ORM\JoinColumn(nullable=true)
     *
     * The file this entity is derived from.
     */
    protected $relatedOriginalFile;

    /**
     * @ORM\OneToMany(targetEntity=File::class, mappedBy="relatedOriginalFile")
     * Files that are created as a derivative of this file (such as composite images with a QR code).
     */
    protected $relatedDerivativeFiles;

    /*
     * @var File
     *
     * @ORM\OneToMany(targetEntity="File", mappedBy="relatedPriorVersion")
     *
     * The new version of the file (if it's replaced) - this allows for version history.
     * (note: its plural "priorVersions", because you technically could have multiple new files that have stem from this one.)
     */
//    private $relatedNewVersions;

    /*
     * @var File
     *
     * @ORM\ManyToOne(targetEntity="File", inversedBy="relatedNewVersions")
     * @ORM\JoinColumn(name="related_prior_version_id", referencedColumnName="id", nullable=true)
     *
     * The prior/"old" version of this file when it is updated/replaced - this allows for version history (and metadata about past file versions)
     */
//    private $relatedPriorVersion;

    /**
     * @return string
     */
    public function getRemoteFilePath(): string
    {
        return $this->remoteFilePath;
    }

    /**
     * @return string
     */
    public function getLocalFilePath(): string
    {
        return $this->localFilePath;
    }

    /**
     * @param string $localFilePath
     */
    public function setLocalFilePath(string $localFilePath): void
    {
        $this->localFilePath = $localFilePath;
    }

    /**
     * @param string $remoteFilePath
     */
    public function setRemoteFilePath(string $remoteFilePath): void
    {
        $this->remoteFilePath = $remoteFilePath;
    }

    public function __construct($filepath, $isDeleted = false, $flaggedForDelete = false)
    {
        if (!is_file($filepath)) {
            throw new \Exception('the file with filepath: "'. $filepath .'" does not exist');
        }

        $this->localFilePath        = $filepath;
        $parts                      = pathinfo($filepath);
        $this->originalBasename     = $parts['basename'];

        $ext                        = pathinfo($filepath, PATHINFO_EXTENSION);
        $this->fileExtension        = $ext;

        $filesizeInBytes            = filesize($filepath);
        $this->filesize             = $filesizeInBytes;

        $checksum = md5_file($filepath);
        $this->contentsCheckSum     = $checksum;

        $this->flaggedForDelete     = $flaggedForDelete;
        $this->isDeleted            = $isDeleted;

        $this->relatedTemplates         = new ArrayCollection();
        $this->relatedDerivativeFiles   = new ArrayCollection();
    }

    /**
     * @return Collection|Template[]
     */
    public function getRelatedTemplates(): Collection
    {
        return $this->relatedTemplates;
    }

    public function addRelatedTemplate(TemplateInterface $relatedTemplate): self
    {
        if (!$this->relatedTemplates->contains($relatedTemplate)) {
            $this->relatedTemplates[] = $relatedTemplate;
            $relatedTemplate->setRelatedOriginalFile($this);
        }

        return $this;
    }

    // check the $template belongs to this file.
    public function hasRelatedTemplate(Template $template, $throwExceptionOnError = true)
    {
        $result = $this->relatedTemplates->contains($template);
        if (!$result && $throwExceptionOnError) {
            throw new \Exception('this file does not contain this template');
        }

        return $throwExceptionOnError;
    }

    public function removeRelatedTemplate(Template $relatedTemplate): self
    {
        if ($this->relatedTemplates->removeElement($relatedTemplate)) {
            // set the owning side to null (unless already changed)
            if ($relatedTemplate->getRelatedOriginalFile() === $this) {
                $relatedTemplate->setRelatedOriginalFile(null);
            }
        }

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDeletionDateTime(): \DateTime
    {
        return $this->deletionDateTime;
    }

    /**
     * @param \DateTime $deletionDateTime
     */
    public function setDeletionDateTime(\DateTime $deletionDateTime): void
    {
        $this->deletionDateTime = $deletionDateTime;
    }

    public function setDeleteDateTimeToNow(): void
    {
        $this->deletionDateTime = new \DateTime('now');
    }

    /**
     * @return string
     */
    public function getFileExtension(): string
    {
        return $this->fileExtension;
    }

    /**
     * @param string $fileExtension
     */
    public function setFileExtension(string $fileExtension): void
    {
        $this->fileExtension = $fileExtension;
    }

    /**
     * @return int
     */
    public function getFilesize(): int
    {
        return $this->filesize;
    }

    /**
     * @param int $filesize
     */
    public function setFilesize(int $filesize): void
    {
        $this->filesize = $filesize;
    }

    /**
     * @return int
     */
    public function getContentsCheckSum(): string
    {
        return $this->contentsCheckSum;
    }

    /**
     * @param string $contentsCheckSum
     */
    public function setContentsCheckSum(string $contentsCheckSum): void
    {
        $this->contentsCheckSum = $contentsCheckSum;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return Person
     */
    public function getRelatedOwnerPerson(): ?Person
    {
        return $this->relatedOwnerPerson;
    }

    /**
     * @param Person $newOwner
     * @param bool $addToRelation
     */
    public function setRelatedOwnerPerson(Person $newOwner, $addToRelation = true): void
    {
        if ($addToRelation) {
            $newOwner->addRelatedFileIOwn($this, false);
        }

        $this->relatedOwnerPerson = $newOwner;
    }

    // used with BaseEntity->outputContents() (for console or testing)
    public function getOutputContents()
    {
        return [
            'remoteFilePath'    => $this->remoteFilePath,
            'originalBasename'  => $this->originalBasename,
            'ownerPerson'       => $this->relatedOwnerPerson->getEmail()
        ];
    }

    public function getImageGD()
    {
        // todo: check it's an image somehow
        return new Image($this->getLocalFilePath());
    }

    public function isImage()
    {
//        if ($this->fileExtension == 'png') {}
    }

    public function __toString()
    {
        return $this->originalbasename;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    /**
     * @param bool $isDeleted
     */
    public function setIsDeleted(bool $isDeleted): void
    {
        $this->isDeleted = $isDeleted;
    }

    /**
     * @return string
     */
    public function getOriginalFilename(): string
    {
        $path_parts = pathinfo($this->originalBasename);

        return $path_parts['filename'];
    }

    /**
     * @param string $originalFilename
     */
    public function setOriginalFilename(string $originalFilename): void
    {
        $this->originalBasename = $originalFilename;
    }

    public function getOriginalBasename()
    {
        $path_parts = pathinfo($this->originalBasename);

        return $path_parts['basename'];
    }

    /**
     * @return FileInterface
     */
    public function getRelatedOriginalFile(): ?FileInterface
    {
        return $this->relatedOriginalFile;
    }

    /**
     * @param FileInterface $relatedOriginalFile
     */
    public function setRelatedOriginalFile(FileInterface $relatedOriginalFile): void
    {
        $this->relatedOriginalFile = $relatedOriginalFile;
    }

    /**
     * @return Collection|File[]
     */
    public function getRelatedDerivativeFiles(): ?Collection
    {
        return $this->relatedDerivativeFiles;
    }

    public function addRelatedDerivativeFile(FileInterface $relatedDerivativeFile): self
    {
        if (!$this->relatedDerivativeFiles->contains($relatedDerivativeFile)) {
            $this->relatedDerivativeFiles[] = $relatedDerivativeFile;
            $relatedDerivativeFile->setRelatedOriginalFile($this);
        }

        return $this;
    }

    public function removeRelatedDerivativeFile(File $relatedDerivativeFile): self
    {
        if ($this->relatedDerivativeFiles->removeElement($relatedDerivativeFile)) {
            // set the owning side to null (unless already changed)
            if ($relatedDerivativeFile->getRelatedOriginalFile() === $this) {
                $relatedDerivativeFile->setRelatedOriginalFile(null);
            }
        }

        return $this;
    }
}
<?php
/*
* created on: 21/11/2021 - 13:24
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Entity\FileManager;

use App\Entity\Person;
use Doctrine\ORM\Mapping as ORM;
use VisageFour\Bundle\ToolsBundle\Entity\BaseEntity;
use Doctrine\ORM\Mapping\MappedSuperclass;

/**
 * @MappedSuperclass
 * Class File
 * @package App\VisageFour\Bundle\ToolsBundle\Entity\FileManager
 *
 * this entity stores details about a file that's created/uploaded. important details like:
 * owner person, file size, original name and even alows for things like version history of a file and duplication detection (via checksum hash)
 */
class File extends BaseEntity
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
     * the remoteFilePath, wherever it's stored (e.g. AWS S3)
     */
    private $remoteFilePath;

    /**
     * @var string
     *
     * @ORM\Column(name="originalFilename", type="string", length=255, nullable=false)
     */
    private $originalFilename;

    /**
     * @var string
     *
     * @ORM\Column(name="fileExtension", type="string", length=10, nullable=true)
     */
    private $fileExtension;

    /**
     * @var integer
     *
     * @ORM\Column(name="filesize", type="integer", nullable=true)
     *
     * Filesize in (bytes?)
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
        $parts = pathinfo($filepath);
        $originalFilename = $parts['basename'];
        $this->originalFilename     = $originalFilename;

        $filesizeInBytes = filesize($filepath);
        $this->filesize             = $filesizeInBytes;

        $checksum = md5_file($filepath);
        $this->contentsCheckSum     = $checksum;

        $this->flaggedForDelete     = $flaggedForDelete;
        $this->isDeleted            = $isDeleted;
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
            'originalFilename'  => $this->originalFilename,
            'ownerPerson'       => $this->relatedOwnerPerson->getEmail()
        ];
    }

    public function __toString()
    {
        return $this->originalFilename;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}
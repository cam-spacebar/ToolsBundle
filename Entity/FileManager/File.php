<?php
/*
* created on: 21/11/2021 - 13:24
* by: Cameron
*/

namespace App\VisageFour\Bundle\ToolsBundle\Entity\FileManager;

use Doctrine\ORM\Mapping as ORM;
use VisageFour\Bundle\ToolsBundle\Entity\BaseEntity;

/**
 * Class File
 * @package App\VisageFour\Bundle\ToolsBundle\Entity\FileManager
 *
 * this entity stores details about a file that's created/uploaded. important details like:
 * owner person, file size, original name and even alows for things like version history of a file and duplication detection (via checksum hash)
 */
class File extends BaseEntity
{
    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="string", length=255)
     *
     * the filename, wherever it's stored (e.g. AWS S3)
     */
    private $filename;

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
     * @var integer
     *
     * @ORM\Column(name="filesize", type="integer", nullable=true)
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
     * @ORM\Column(name="deletionDateTime", type="datetime")
     */
    private $deletionDateTime;

    /**
     * @var File
     *
     * @ORM\OneToMany(targetEntity="File", mappedBy="priorVersions")
     *
     * The new version of the file (if it's replaced) - this allows for version history.
     * (note: its plural "priorVersions", because you technically could have multiple new files that have stem from this one.)
     */
    private $newVersions;

    /**
     * @var File
     *
     *
     *
     * The new version of the file (if it's replaced) - this allows for version history.
     */
    private $priorVersion;
}
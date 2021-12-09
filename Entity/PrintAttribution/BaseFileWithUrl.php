<?php
/*
* created on: 02/12/2021 - 12:53
* by: Cameron
*/

namespace App\VisageFour\Bundle\ToolsBundle\Entity\PrintAttribution;

use VisageFour\Bundle\ToolsBundle\Entity\FileManager\BaseFile;
use Doctrine\ORM\Mapping as ORM;
use VisageFour\Bundle\ToolsBundle\Entity\PrintAttribution\TrackedFile;
use Doctrine\ORM\Mapping\MappedSuperclass;
use VisageFour\Bundle\ToolsBundle\Interfaces\PrintAttribution\FileInterface;

/**
 * @MappedSuperclass
 *
 * Class BaseFileWithUrl
 * @package App\VisageFour\Bundle\ToolsBundle\Entity\PrintAttribution
 *
 * This adds the TrackedFile entity to the File entity.
 */
class BaseFileWithUrl extends BaseFile implements FileInterface
{
    /**
     * @ORM\OneToOne(targetEntity=TrackedFile::class, mappedBy="relatedFile", cascade={"persist", "remove"})
     */
    private $relatedTrackedFile;

    public function getRelatedTrackedFile(): ?TrackedFile
    {
        return $this->relatedTrackedFile;
    }

    public function setRelatedTrackedFile(TrackedFile $relatedTrackedFile): self
    {
        // set the owning side of the relation if necessary
        if ($relatedTrackedFile->getRelatedFile() !== $this) {
            $relatedTrackedFile->setRelatedFile($this);
        }

        $this->relatedTrackedFile = $relatedTrackedFile;

        return $this;
    }
}
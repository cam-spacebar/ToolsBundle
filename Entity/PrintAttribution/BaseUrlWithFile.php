<?php
/*
* created on: 30/11/2021 - 17:51
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Entity\PrintAttribution;

use VisageFour\Bundle\ToolsBundle\Entity\UrlShortener\BaseUrl;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping as ORM;
use VisageFour\Bundle\ToolsBundle\Entity\PrintAttribution\TrackedFile;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Class BaseUrlWithFile
 * @package VisageFour\Bundle\ToolsBundle\Entity\UrlShortener
 *
 * @MappedSuperclass
 *
 * This entity adds TrackedFile to the Url entity.
 * Initially designed for use with generating posters with QR codes.
 */
class BaseUrlWithFile extends BaseUrl
{
    /**
     * @ORM\ManyToOne(targetEntity=TrackedFile::class, inversedBy="relatedUrls")
     * @ORM\JoinColumn(nullable=true)
     *
     */
    private $trackedFile;

//    public function __construct(string $urlRedirect, string $shortenedCode, string $shortUrl)
//    {
//        parent::__construct($urlRedirect, $shortenedCode, $shortUrl);
//    }

    /**
     * @return TrackedFile
     */
    public function getTrackedFile(): TrackedFile
    {
        return $this->trackedFile;
    }

    public function setTrackedFile(TrackedFile $trackedFile): self
    {
        $this->trackedFile = $trackedFile;

        return $this;
    }
}
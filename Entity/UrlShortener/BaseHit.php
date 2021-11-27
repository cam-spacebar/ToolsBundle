<?php
/*
* created on: 26/11/2021 - 15:32
* by: Cameron
*/

namespace App\VisageFour\Bundle\ToolsBundle\Entity\UrlShortener;

use Doctrine\ORM\Mapping as ORM;

use VisageFour\Bundle\ToolsBundle\Entity\BaseEntity;
use App\Entity\UrlShortener\Url;
use Doctrine\ORM\Mapping\MappedSuperclass;

/**
 * Class BaseHit
 * @package App\VisageFour\Bundle\ToolsBundle\Entity\UrlShortener
 *
 * @MappedSuperclass
 */
class BaseHit extends BaseEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=15)
     */
    protected $ipAddress;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $httpUserAgent;

    /**
     * @ORM\ManyToOne(targetEntity=Url::class, inversedBy="relatedHits")
     */
    protected $relatedUrl;

    public function __construct(Url $relatedUrl, string $ipAddress, string $httpUserAgent)
    {
        $this->ipAddress        = $ipAddress;
        $this->httpUserAgent    = $httpUserAgent;
        $this->relatedUrl       = $relatedUrl;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function getHttpUserAgent(): ?string
    {
        return $this->httpUserAgent;
    }

    public function setHttpUserAgent(string $httpUserAgent): self
    {
        $this->httpUserAgent = $httpUserAgent;

        return $this;
    }

    public function getRelatedUrl(): ?Url
    {
        return $this->relatedUrl;
    }

    public function setRelatedUrl(?Url $relatedUrl): self
    {
        $this->relatedUrl = $relatedUrl;

        return $this;
    }

    public function getOutputContents ()
    {
        return ([
            'ipAddress'             => $this->ipAddress,
            'httpUserAgent'         => $this->httpUserAgent,
            'destination URL'       => $this->relatedUrl->getUrlRedirect()
        ]);
    }
}
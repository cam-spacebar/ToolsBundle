<?php
/*
* created on: 26/11/2021 - 14:06
* by: Cameron
*/


namespace App\VisageFour\Bundle\ToolsBundle\Entity\UrlShortener;

use App\Entity\UrlShortener\Hit;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\Common\Collections\Collection;
use VisageFour\Bundle\ToolsBundle\Entity\Code;

/**
 * Class BaseUrl
 * @package App\VisageFour\Bundle\ToolsBundle\Entity\UrlShortener
 *
 * @MappedSuperclass
 */
class BaseUrl extends Code
{
    /**
     * @ORM\Column(type="string", length=1024)
     *
     * The url that the user will be redirected to.
     */
    protected $urlRedirect;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity=Hit::class, mappedBy="relatedUrl")
     */
    private $relatedHits;

    public function __construct(string $urlRedirect, string $shortenedCode)
    {
        $this->relatedHits      = new ArrayCollection();
        $this->urlRedirect      = $urlRedirect;
        $this->code             = $shortenedCode;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrlRedirect(): ?string
    {
        return $this->urlRedirect;
    }

    public function setUrlRedirect(string $urlRedirect): self
    {
        $this->urlRedirect = $urlRedirect;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|Hit[]
     */
    public function getRelatedHits(): Collection
    {
        return $this->relatedHits;
    }

    public function addRelatedHit(Hit $relatedHit): self
    {
        if (!$this->relatedHits->contains($relatedHit)) {
            $this->relatedHits[] = $relatedHit;
            $relatedHit->setRelatedUrl($this);
        }

        return $this;
    }

    public function removeRelatedHit(Hit $relatedHit): self
    {
        if ($this->relatedHits->removeElement($relatedHit)) {
            // set the owning side to null (unless already changed)
            if ($relatedHit->getRelatedUrl() === $this) {
                $relatedHit->setRelatedUrl(null);
            }
        }

        return $this;
    }

    public function getHitCount() {
        return $this->getRelatedHits()->count();
    }

    public function getOutputContents ()
    {

        return ([
            'destination URL'       => $this->urlRedirect,
            'shortened Code'        => $this->shortenedCode,
            'hit count'             => $this->getHitCount()
        ]);
    }
}
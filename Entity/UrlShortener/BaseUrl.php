<?php
/*
* created on: 26/11/2021 - 14:06
* by: Cameron
*/


namespace App\VisageFour\Bundle\ToolsBundle\Entity\UrlShortener;

use Doctrine\ORM\Mapping as ORM;

class BaseUrl
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=1024)
     */
    protected $url;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=32)
     */
    protected $shortenedCode;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

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

    public function getShortenedCode(): ?string
    {
        return $this->shortenedCode;
    }

    public function setShortenedCode(string $shortenedCode): self
    {
        $this->shortenedCode = $shortenedCode;

        return $this;
    }
}
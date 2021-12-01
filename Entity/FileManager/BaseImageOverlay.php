<?php
/*
* created on: 26/11/2021 - 11:41
* by: Cameron
*/

namespace App\VisageFour\Bundle\ToolsBundle\Entity\FileManager;

use Doctrine\ORM\Mapping\MappedSuperclass;
use VisageFour\Bundle\ToolsBundle\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\FileManager\Template;

/**
 * @MappedSuperclass
 *
 * Class BaseImageOverlay
 * @package App\VisageFour\Bundle\ToolsBundle\Entity\FileManager
 *
 * Allows an image (QR code) to be positioned onto a template (a File)
 * e.g. a QR code onto a promotional poster image or 5 QR codes onto a printable PDF.
 */
class BaseImageOverlay extends BaseEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity=Template::class, inversedBy="relatedImageOverlays")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $relatedTemplate;

    /**
     * @ORM\Column(type="string", length=10)
     */
    protected $type;

    // all possible 'types' of overlays
    const TYPE_QRCode = 'TYPE_QRCODE';

    /**
     * @ORM\Column(type="integer")
     *
     * Starting X position of image overlay
     */
    protected $xCoord;

    /**
     * @ORM\Column(type="integer")
     * Starting Y position of image overlay
     */
    protected $yCoord;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $width;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $height;

    /**
     * @ORM\Column(type="string", nullable=false)
     *
     * This corresponds to the (batch entity) payload key - the batch->payload provides a such as: "coupon_QR_CODE" and a value of "http://www.xyz.com?coupon=123" for images
     * (or a label of: "page_5_activity_QR_code" for PDFs)
     */
    protected $labelName;

    public function __construct(Template $template, int $posX, int $posY, int $w, int $h, string $labelName, $type = self::TYPE_QRCode)
    {
        $this->setRelatedTemplate($template)
            ->setXCoord($posX)
            ->setYCoord($posY)
            ->setWidth($w)
            ->setHeight($h)
            ->setLabelName($labelName)
            ->setType($type)
        ;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getXCoord(): ?int
    {
        return $this->xCoord;
    }

    public function setXCoord(int $xCoord): self
    {
        $this->xCoord = $xCoord;

        return $this;
    }

    public function getYCoord(): ?int
    {
        return $this->yCoord;
    }

    public function setYCoord(int $yCoord): self
    {
        $this->yCoord = $yCoord;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(?int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function setLabelName(?string $labelName): self
    {
        $this->labelName = $labelName;

        return $this;
    }
}
<?php
/*
* created on: 29/11/2021 - 20:40
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Classes\ImageOverlay;

class Image
{
    // the image resource
    private $src;

    private $width;
    private $height;

    public function __construct(?string $filepath, $src = null)
    {
        if (!empty($filepath)) {
            $this->src = imagecreatefrompng($filepath);
        } else {
            $this->src      = $src;
        }

        $this->width    = imagesx($this->src);
        $this->height   = imagesy($this->src);

    }

    /**
     * @return false|int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return false|int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @return false|resource
     */
    public function getSrc()
    {
        return $this->src;
    }
}
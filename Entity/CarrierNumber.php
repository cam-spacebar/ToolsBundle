<?php

namespace VisageFour\Bundle\ToolsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * CarrierNumber
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="VisageFour\Bundle\ToolsBundle\Repository\CarrierNumberRepository")
 */
class CarrierNumber
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="create")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updatedAt", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     */
    protected $updatedAt;

    /**
     * @var string
     *
     * @ORM\Column(name="number", type="string", length=25)
     */
    protected $number;

    /**
     * @var string
     *
     * @ORM\Column(name="reference", type="string", length=50, nullable=false)
     */
    protected $reference;

    /**
     * @var boolean
     *
     * @ORM\Column(name="SmsCapable", type="boolean")
     */
    protected $SmsCapable;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=100)
     */
    protected $country;

    /**
     * @var string
     *
     * @ORM\Column(name="vendor", type="string", length=100)
     */
    protected $vendor;
    
    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param $number
     * @return $this
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isSmsCapable()
    {
        return $this->SmsCapable;
    }

    /**
     * @param $SmsCapable
     * @return $this
     */
    public function setSmsCapable($SmsCapable)
    {
        $this->SmsCapable = $SmsCapable;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param $country
     * @return $this
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return string
     */
    public function getVendor()
    {
        return $this->vendor;
    }

    /**
     * @param string $vendor
     */
    public function setVendor($vendor)
    {
        $this->vendor = $vendor;

        return $this;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param string $reference
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }
}
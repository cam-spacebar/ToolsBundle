<?php

namespace VisageFour\Bundle\ToolsBundle\Entity;

use VisageFour\Bundle\ToolsBundle\Interfaces\BaseEntityInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 *
 */
abstract class BaseEntity implements BaseEntityInterface
{
    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="createdAt", type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updatedAt", type="datetime")
     */
    protected $updatedAt;

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return BaseEntityInterface
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return BaseEntityInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    const LOG_DETAIL_NONE       = 0;
    const LOG_DETAIL_BASIC      = 1;
    const LOG_DETAIL_MORE       = 2;

    // used to implode an array with logging data into a string that can be printed.
    // just a cleaner alternative to dump() - particularly if there's a lot of information.
    protected function implodeLoggingExtraDataArray (array $arr) {
        $text='';
        $firstLoop = true;
        foreach ($arr as $curProperty => $curValue) {
            if (!$firstLoop) {
                $text .= ', ';
            }
            $firstLoop = false;
            $text .= $curProperty .': ' . $curValue;
        }

        return $text;
    }

    // outputs the important details about the object, specifically for console (for use in terminal, either: fixtures or testing)
    // you can override this if you want to provide a custom output (ussually this is useful for larger/complex entities with relations such as: ToolsBundle::Checkout)
    public function outputContents($lineBreak = "\n")
    {
        $lb = $lineBreak;
        $className = get_class($this);
        if (!method_exists($this, '__toString')) {
            throw new \Exception('no __ToString method found for entity: '. $className .'. This is needed for outputContents().');
        }
        print $className .' contents: '. $this . $lb;
        if (method_exists($this, 'getOutputContents')) {
            $data = $this->getOutputContents();

            foreach ($data as $fieldName => $value) {
                print ' - '. $fieldName . ': '. $value .$lb;
            }
            print $lb;
        } else {
            throw new \Exception('unable to outputContents(), as the method: getOutputContents() does not exist on the entity: '. $className
                .'. Please create it, or alternatively (For more complex entities, you can override outputContents() for a custom appearance.');
        }
    }

    public function getShortName()
    {
        return (new \ReflectionClass($this))->getShortName();
    }
}
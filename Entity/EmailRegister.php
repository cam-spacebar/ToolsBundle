<?php

namespace VisageFour\Bundle\ToolsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping\MappedSuperclass;

/**
 * EmailRegister
 *
 * @MappedSuperclass
 */
class EmailRegister
{
    // used for SendStatus
    const SPOOLED           = 0;
    const SENT              = 1;

    // used for adapter
    const LEXIK_ADAPTER     = 1;

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
     * @ORM\Column(name="updatedAt", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     */
    protected $updatedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="create")
     */
    protected $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(name="toEmail", type="string", length=255)
     */
    protected $toEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", length=10)
     */
    protected $locale;

    /**
     * @var string
     *
     * @ORM\Column(name="paramsSerialized", type="string", length=900)
     */
    protected $paramsSerialized;

    /**
     * @var integer
     *
     * @ORM\Column(name="sendStatus", type="integer")
     */
    protected $sendStatus;

    /**
     * @var integer
     *
     * @ORM\Column(name="adapter", type="integer")
     *
     * this is the final system used to send the email (lexik bundle, swiftmail or whatever else might be defined)
     */
    protected $adapter;

    /**
     * @var string
     *
     * @ORM\Column(name="emailTemplate", type="string", length=255)
     */
    protected $emailTemplate;

    /**
     * @var string
     *
     *
     */
    protected $params;
    
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
     * Set toEmail
     *
     * @param string $toEmail
     *
     * @return EmailRegister
     */
    public function setToEmail($toEmail)
    {
        $this->toEmail = $toEmail;

        return $this;
    }

    /**
     * Get toEmail
     *
     * @return string
     */
    public function getToEmail()
    {
        return $this->toEmail;
    }

    /**
     * Set locale
     *
     * @param string $locale
     *
     * @return EmailRegister
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set params
     *
     * @param string $params
     *
     * @return EmailRegister
     */
    public function setParams($params)
    {
        $this->params = $params;

        $this->paramsSerialized = serialize($params);

        return $this;
    }

    /**
     * Get params
     *
     * @return string
     */
    public function getParams()
    {
        $this->params = unserialize($this->getParamsSerialized());

        return $this->params;
    }

    /**
     * @return string
     */
    public function getParamsSerialized()
    {
        return $this->paramsSerialized;
    }

    /**
     * @return string
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @param string $adapter
     */
    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @return string
     */
    public function getEmailTemplate()
    {
        return $this->emailTemplate;
    }

    /**
     * @param string $emailTemplate
     */
    public function setEmailTemplate($emailTemplate)
    {
        $this->emailTemplate = $emailTemplate;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @param string $paramsSerialized
     */
    private function setParamsSerialized($paramsSerialized)
    {
        $this->paramsSerialized = $paramsSerialized;
    }

    /**
     * @return string
     */
    public function getSendStatus()
    {
        return $this->sendStatus;
    }

    /**
     * @param string $sendStatus
     */
    public function setSendStatus($sendStatus)
    {
        $this->sendStatus = $sendStatus;
    }

    public function __construct ()
    {
    }

    public function configure ($to, $params, $emailTemplate, $locale = 'en', $adapter = SELF::LEXIK_ADAPTER)
    {
        $this->setToEmail(          $to);
        $this->setParams(           $params);
        $this->setEmailTemplate(    $emailTemplate);
        $this->setLocale(           $locale);
        $this->setAdapter(          $adapter);
    }
}
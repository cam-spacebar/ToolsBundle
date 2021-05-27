<?php

namespace VisageFour\Bundle\ToolsBundle\Entity;

use VisageFour\Bundle\ToolsBundle\Services\Security\AppSecurity;
use App\Services\FrontendUrl;
use VisageFour\Bundle\ToolsBundle\Services\PasswordManager;
use App\Twencha\Bundle\EventRegistrationBundle\Exceptions\ApiErrorCode;
use VisageFour\Bundle\ToolsBundle\Exceptions\AccountAlreadyVerified;
use VisageFour\Bundle\ToolsBundle\Exceptions\AccountNotRegisteredException;
use VisageFour\Bundle\ToolsBundle\Exceptions\AccountNotVerifiedException;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Twencha\Bundle\EventRegistrationBundle\Model\BasePersonInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use JsonSerializable;
use VisageFour\Bundle\ToolsBundle\Entity\BaseEntity;
use VisageFour\Bundle\ToolsBundle\Interfaces\CanNormalize;
use VisageFour\Bundle\ToolsBundle\Statics\StaticInternational;
use Doctrine\ORM\Mapping\MappedSuperclass;


/*
 * BasePerson
 *test
 * todo: strip out parts of this that belonged to PersonBundle
 *
 * @ORM\Table(name="BasePerson")
 * @ORM\Entity(repositoryClass="VisageFour\Bundle\PersonBundle\Repository\basePersonRepository")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="discr", type="string")
 * @DiscriminatorMap({"baseperson" = "BasePerson"s "photographer" = "Platypuspie\AnchorcardsBundle\Entity\Photographer" })
 *
 * @UniqueEntity(fields="usernameCanonical", errorPath="username", message="fos_user.username.already_used")
 * @ORM\AttributeOverrides({
 *      @ORM\AttributeOverride(name="email", column=@ORM\Column(type="string", name="email", length=255, unique=false, nullable=true)),
 *      @ORM\AttributeOverride(name="emailCanonical", column=@ORM\Column(type="string", name="email_canonical", length=255, unique=false, nullable=true))
 * })
 */
/**
 * Class BasePerson
 * @MappedSuperClass
 */
class BasePerson extends BaseEntity implements BasePersonInterface, JsonSerializable, CanNormalize
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"zapierSpreadsheet"})
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
     * @ORM\Column(name="title", type="string", length=5, nullable=true)
     * @Assert\NotBlank(groups={"registration"}, message="Title must be entered")
     * @Assert\Choice(
     *     choices = { "mr", "ms", "mrs" },
     *     message = "Choose a valid title"
     * )
     * @Groups({"zapierSpreadsheet"})
     */
    protected $title;

    /**
     * What is this for?
     *
     * @var int
     *
     * @ORM\Column(name="isRegistered", type="boolean", nullable=true)
     */
    protected $isRegistered;

    /**
     * Has the user verified that their email address is real?
     *
     * @var string
     *
     * @ORM\Column(name="isVerified", type="boolean", nullable=false)
     */
    protected $isVerified;

    /**
     * The account verification token. The random string that is sent to a new user's email address to verify it's real
     *
     * @var string
     *
     * @ORM\Column(name="verification_token", type="string", nullable=false)
     */
    protected $verificationToken;

    /**
     * this is needed to change the users password (if not logged in - due to using the "forgot my password" method).
     *
     * @var string
     *
     * @ORM\Column(name="change_password_token", type="string", nullable=true)
     *
     * Should be set to null straight after using the token (i.e. saving a new password).
     * A new one is created only when someone requests the "forgot my password" form.
     */
    protected $changePasswordToken;

    /**
     * @ORM\Column(type="string", length=255)
     * This must be the encoded string (not the raw password string)
     * To encode, use: PasswordManager->validatePasswordAndEncode()
     */
    private $password;

    /**
    `* @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string
     *
     * @ORM\Column(name="firstName", type="string", length=40, nullable=true)
     * @Groups({"zapierSpreadsheet"})
     * @Assert\NotBlank(groups={"registration"}, message="Name must be entered")
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="lastName", type="string", length=20, nullable=true)
     * @Assert\NotBlank(groups={"detailed"}, message="Last name must be entered")
     * @Groups({"zapierSpreadsheet"})
     */
    protected $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="mobileNumber", type="string", length=75, unique=false, nullable=true)
     * @Groups({"zapierSpreadsheet"})
     * @Assert\NotBlank(groups={"registration"}, message="Mobile number must be entered")
     */
    protected $mobileNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="mobileNumberCanonical", type="string", length=75, unique=true, nullable=true)
     */
    protected $mobileNumberCanonical;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=100, unique=false, nullable=true)
     * @Groups({"zapierSpreadsheet"})
     * @Assert\NotBlank(groups={"registration"}, message="Email address must be entered")
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(name="emailCanonical", type="string", length=100, unique=false, nullable=true)
     * @Groups({"zapierSpreadsheet"})
     */
    protected $emailCanonical;

    /**
     * @var string
     *
     * @ORM\Column(name="suburb", type="string", length=70, unique=false, nullable=true)
     * @Assert\NotBlank(groups={"detailed"}, message="Suburb must be entered")
     * @Assert\Length(min=2,max=70)
     * @Groups({"zapierSpreadsheet"})
     */
    protected $suburb;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=50, unique=false, nullable=true)
     * @Assert\NotBlank(groups={"detailed"}, message="City  must be entered")
     * @Assert\Length(min=2,max=50)
     * @Groups({"zapierSpreadsheet"})
     */
    protected $city;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=50, unique=false, nullable=true)
     * @Assert\NotBlank(groups={"detailed"}, message="Country must be entered")
     * @Assert\Length(min=2,max=50)
     * @Groups({"zapierSpreadsheet"})
     */
//    protected $countryCode;       // replaced with $countryOfOrigin

    /**
     *
     * @var string
     *
     * @ORM\Column(name="country_of_origin", type="string", length=2, nullable=true)
     *
     * corresponds to StaticInternational::$countries array (i.e. is a 2 character code)
     */
    protected $countryOfOrigin;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return BasePerson
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        $fName = $this->firstName;
        return (empty($fName)) ? 'no name' : $fName;
    }

    public function getFullName () {
        if (empty($this->getLastName())) {
            return $this->getFirstName();
        } else {
            return $this->getFirstName() .' '. $this->getLastName();
        }
    }

    public function getFullNameOrPlaceholder () {
        if (empty($this->getFirstName())) {
            return '[No Name]';
        } else {
            return $this->getFullName();
        }
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return BasePerson
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->isTitleValid($title, true);
        $this->title = $title;
    }

    public function isTitleValid ($title, $throwExceptionOnInvalid = false)
    {
        $acceptedTitles = array (
            'ms',
            'mrs',
            'mr'
        );

        if (!in_array($title, $acceptedTitles)) {
            if ($throwExceptionOnInvalid) {
                throw new \Exception('person->title: "'. $title .'" is not known / accepted');
            }
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getMobileNumber()
    {
        return $this->mobileNumber;
    }

    /**
     * @param string $mobileNumber
     */
    public function setMobileNumber($mobileNumber)
    {
        $this->setMobileNumberCanonical($this->canonicalizeMobileNumber($mobileNumber));
        $this->mobileNumber = $mobileNumber;
    }

    public function canonicalizeMobileNumber ($mobileNumber) {
        return str_replace(' ', '', $mobileNumber);
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
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getSuburb()
    {
        return $this->suburb;
    }

    /**
     * @param string $suburb
     */
    public function setSuburb($suburb)
    {
        $this->suburb = $suburb;
    }

    public function __construct () {
        $this->createVerificationHash();

    }

    /**
     * @return string $emailCanonical
     */
    public function getEmailCanonical()
    {
        return $this->emailCanonical;
    }

    static public function canonicalizeEmail ($email) {
        return trim(strtolower($email));
    }

    /**
     * @return string $email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
        $this->emailCanonical = self::canonicalizeEmail($email);
    }

    /**
     * @return int
     */
    public function isRegistered()
    {
        return $this->isRegistered;
    }

    public function isRegisteredAsString()
    {
        return ($this->isRegistered()) ? 'Yes' : 'No';
    }

    /**
     * @param int $isRegistered
     */
    public function setIsRegistered($isRegistered)
    {
        $this->isRegistered = $isRegistered;

        return $this;
    }

    /**
     * @return string
     */
    public function getMobileNumberCanonical()
    {
        return $this->mobileNumberCanonical;
    }

    /**
     * @param string $mobileNumberCanonical
     */
    public function setMobileNumberCanonical($mobileNumberCanonical)
    {
        $this->mobileNumberCanonical = $mobileNumberCanonical;
    }

    public function jsonSerialize()
    {
        $createdAt = (!empty($createdAt)) ? $this->createdAt->format('Y-m-d g:i s') : null  ;
        return array(
            'id'            => $this->id,
            'createdAt'     => $createdAt,
            'title'         => $this->title,
            'firstName'     => $this->firstName,
            'lastName'      => $this->lastName,
            'email'         => $this->emailCanonical,
            'mobile'        => $this->mobileNumber,
            'suburb'        => $this->suburb,
            'city'          => $this->city,
        );
    }

    public function normalize()
    {
        $this->jsonSerialize();
    }

    /**
     * @return mixed
     */
    public function getCountryOfOrigin()
    {
        return $this->countryOfOrigin;
    }

    /**
     * @param mixed $countryOfOrigin
     */
    public function setCountryOfOrigin($countryOfOrigin)
    {
        StaticInternational::checkCountryExistsByCode($countryOfOrigin);

        $this->countryOfOrigin = $countryOfOrigin;

        return $this;
    }

    public function getCountryOfOriginAsString () {
        if (empty($this->countryOfOrigin)) {
            return "no country set";
        }
        $asStr = StaticInternational::getCountryNameByCode($this->countryOfOrigin);

        return $asStr;
    }

    public function checkCountryOfOriginIsSet($throwExceptionIfNull = true)
    {
        if (empty($this->countryOfOrigin)) {
            if ($throwExceptionIfNull) {
                throw new \Exception ('person object must has "country of origin" set to perform this function.');
            }
        }
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @see UserInterface
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Use PasswordManager->validatePasswordAndEncode() to set this!s
     */
    public function setPassword(string $encodedPassword): self
    {
        $this->password = $encodedPassword;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using bcrypt or argon
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function hasPasswordBeenSet()
    {
        if ($this->password == PasswordManager::PASSWORD_NOT_INITIALIZED) {
            return true;
        }

        return false;
    }

    public function setAccountIsVerified(bool $isVerified): self
    {
        if ($isVerified && !$this->isRegistered()) {
            throw new AccountNotRegisteredException($this->email);
        }

        $this->isVerified = $isVerified;

        return $this;
    }

    public function isVerified($throw = false): bool
    {
        $result = $this->isVerified;
        if ((!$result) && $throw) {
            throw new AccountNotVerifiedException();
        }

        return $result;
    }

    /**
     * @return string
     */
    public function checkVerificationToken(string $token, $throwExceptionOnError = true): bool
    {
        if ($this->isVerified && $throwExceptionOnError) {
            throw new AccountAlreadyVerified();
        }

//        print 'token verification test: '. $this->verificationToken .' == '. $token;
        if ($this->verificationToken == $token) {
            // Signal the account has been verified.
            $this->setAccountIsVerified(true);
            // note: don't need to delete the token (to prevent re-use and then reset of password), as this method first checks for isVerified() and wil throw an error).

            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $verificationToken
     */
    public function createVerificationHash(): string
    {

        $salt = '34rvefdnvasADWEIREcvwf2io4nwecsDC';

        $hash = md5($salt.$this->getEmailCanonical());
//        print 'verification token just created: '. $hash;
        $this->verificationToken = $hash;

        return $hash;
    }

    public function getVerificationToken()
    {
        return $this->verificationToken;
    }

    /**
     * @return string
     */
    public function createChangePasswordToken(): string
    {
        $salt = 'randomsaltsc23irn2#C@wrci243wc';

        $hash = md5($salt.$this->getEmailCanonical());

        $this->changePasswordToken = $hash;

        return $hash;
    }

    /**
     * @param string $changePasswordToken
     */
    public function isChangePasswordTokenCorrect(string $token): bool
    {
        if ($this->changePasswordToken == $token) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getChangePasswordToken(): string
    {
        return $this->changePasswordToken;
    }

    public function isLoggedIn()
    {
        throw new \Exception(
            'you must use: AppSecurity->getLoggedInUser() to get the loggedIn user. '.
            'Or use CustomController->getLoggedInPerson(). '.
            'You may also consider using: getLoggedInUserOrRedirectToLogin() - as you probably want to cause a redirect.'
        );
    }
}
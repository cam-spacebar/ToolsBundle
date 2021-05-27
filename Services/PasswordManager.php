<?php

namespace VisageFour\Bundle\ToolsBundle\Services;

/*
 * This class is used to:
 * - Check validity of passwords
 * -
 */

use App\Entity\Person;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use VisageFour\Bundle\ToolsBundle\Exceptions\PasswordValidationException;

/**
 * Class PasswordManager
 * @package App\Services
 *
 * manages the creation, validation and encoding of user passwords
 */
class PasswordManager
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    // Setting password to this (instead of allowing password nullable="true") means that it's clear what the password it, instead of an ambiguous
    // (and potentially buggy) "null" for password.
    const PASSWORD_NOT_INITIALIZED = 'pass_not_init_3cwec23wefrsEWC324cwC$4fdf';

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function isPasswordValid(UserInterface $person, string $password) {
        return $this->passwordEncoder->isPasswordValid($person, $password);
    }

    public function validatePasswordCriteria (string $newPassword) {
//        throw new \Exception('this code is not implemented yet 112341234132');

        // check password isn't a "reserved value"
        if ($newPassword == self::PASSWORD_NOT_INITIALIZED) {
            // todo: create a custom class (that can be caught)
            throw new PasswordValidationException( 'You cannot use a password with the value provided. Please use a different password.');
        }

        $extraText =  ' Please ensure you pick a secure password.';

        // check password is longer than 8 characters
        $length = strlen($newPassword);
        $min = 8;
        if ($length < $min) {
            throw new PasswordValidationException("You must provide a password longer than $min characters.". $extraText);
        }

        $max = 70;
        if ($length > 70) {
            throw new PasswordValidationException("You must provide a password less than $max characters.". $extraText);
        }

        return true;
    }

    /*
     * validate the password (is correct length, has a number for example etc)
     * and return the encoded string.
     */
    public function validatePasswordAndEncode (Person $person, string $newPassword): self {
        $this->validatePasswordCriteria($newPassword);

        // encode the password (and return it)

        $encodedPassword = $this->encodePassword($person, $newPassword);
        $person->setPassword($encodedPassword);

        return $this;
    }

    /**
     * @param Person $person
     * @param string $newPassword
     * @return string
     * 
     * if setting password to: self::$PASSWORD_NOT_INITIALIZED, use this method
     * instead of going via (validatePasswordAndEncode) as that would trigger an exception.
     *
     * But generally, don't use this method.
     */
    public function encodePassword (Person $person, string $newPassword): string {
        return $this->passwordEncoder->encodePassword($person, $newPassword);
    }
}
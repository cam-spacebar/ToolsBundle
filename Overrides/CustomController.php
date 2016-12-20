<?php
/**
 * Created by Cameron Burns.
 * Date: 28/09/2016
 */

namespace VisageFour\Bundle\ToolsBundle\Overrides;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Platypuspie\AnchorcardsBundle\Entity\Person;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CustomController extends Controller
{
    /*
    IMPLEMENTATION CODE:
    
    $accessRole = 'SEND_PHOTOS';
    $this->checkAccess ($accessRole);
    $thisPerson = $this->getThisPerson();
    */

    // creates a new Person object that matches to a user obj
    // need to pass in the applications extended PersonManager for FindOneBy method - although BasePersonRepo will act as an interface
    public function getPersonLoggedInOrCreate () {
        $personManager  = $this->getPersonManager();
        $person         = $this->getLoggedInPerson();

        if (empty($person)) {
            // create new person if email address exists
            $thisUser = $this->getUserLoggedIn();

            $logger = $this->getLogger();
            $logger->info('User with username: "'. $thisUser->getUsername() .'" does not have a related Person object. Attempting to create');

            if (!empty($thisUser->getEmail())) {
                // check if person with email address already exists
                $person = $personManager->findByEmail ($thisUser->getEmail());

                if (!empty($person)) {
                    throw new \Exception ('trying to create person with email address: "'. $thisUser->getEmail() .'" however a person with this email address already exists');
                }

                $logger->info('creating new $person obj as one did not exist for $user object.');


                /** @var Person $person */
                $person = $personManager->createNew();
                $person->setEmail($thisUser->getEmail());

                $person->setRelatedUser($thisUser);
                $thisUser->setRelatedPerson($person);

                $em = $this->getEm();

                $em->persist($person);
                $em->persist($thisUser);
                $em->flush();
            } else {
                throw new \Exception ('User does not have a related person object and could not find email address from user object to create a new person.');
            }
        }

        return $person;
    }

    // return true if is in dev environment
    public function isDevEnvironment () {
        if ($this->container->get('kernel')->getEnvironment() == 'dev') {
            return true;
        }

        return false;
    }

    public function getSiteName () {
        $this->container->getParameter('site_name');
    }

    public function getSiteURL () {
        $this->container->getParameter('site_url');
    }

    public function getNoReplyEmail () {
        $this->container->getParameter('email_noreply');
    }



    public function checkAccess ($role) {
        $userSecurity   = $this->getUserSecurity();
        $userSecurity->checkRole ($role);

        return true;
    }

    public function getLoggedInPerson () {
        $userSecurity   = $this->getUserSecurity();
        $thisPerson     = $userSecurity->getPersonLoggedIn();
        return $thisPerson;
    }

    public function getUserLoggedIn () {
        $userSecurity   = $this->getUserSecurity();
        $thisUser       = $userSecurity->getUserLoggedIn();
        return $thisUser;
    }

    /**
     * @return Logger
     */
    public function getLogger () {
        return $this->container->get('logger');
    }



    // METHODS TO OVERRIDE IN APP'S IMPLEMENTATION
    // todo: should be made abstract?
    /**
     * @return \VisageFour\Bundle\PersonBundle\Services\UserSecurity
     */
    public function getUserSecurity () {
        return $this->container->get('usersecurity');
    }

    /**
     * @return \Platypuspie\AnchorcardsBundle\Services\PersonManager
     */
    public function getPersonManager () {
        return $this->container->get('platypuspie.personmanager');
    }

    /**
     * @return EntityManager
     */
    public function getEM () {
        /** @var EntityManager $em */
        return $this->container->get('doctrine.orm.default_entity_manager');
    }
}
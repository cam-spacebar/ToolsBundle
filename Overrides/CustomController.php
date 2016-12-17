<?php
/**
 * Created by Cameron Burns.
 * Date: 28/09/2016
 */

namespace VisageFour\Bundle\ToolsBundle\Overrides;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CustomController extends Controller
{
    /*
    IMPLEMENTATION CODE:
    
    $accessRole = 'SEND_PHOTOS';
    $this->checkAccess ($accessRole);
    $thisPerson = $this->getThisPerson();

    */
    //this will check the user has the role specified in the parameters
    public function checkAccess ($role) {
        die ('needs to be configured for more generalized use - code below from anchorcards app');
        /** @var $userSecurity \VisageFour\Bundle\PersonBundle\Services\UserSecurity */
        $userSecurity   = $this->container->get('usersecurity');
        $userSecurity->checkRole ($role);

        return true;
    }

    // will return the person that corresponds to the user account - you will need to make sure the relationships correctly.
    // if the person doesn't exist, this method should create one for the user and return it.
    public function getThisPerson () {
        die ('needs to be configured for more generalized use - code below from anchorcards app');
        /** @var $userSecurity \VisageFour\Bundle\PersonBundle\Services\UserSecurity */
        $userSecurity   = $this->container->get('usersecurity');
        $thisPerson     = $userSecurity->getPersonLoggedIn();

        if (empty($thisPerson)) {
            // create new person if email address exists
            $thisUser = $userSecurity->getUserLoggedIn();

            if (!empty($thisUser->getEmail())) {
                /** @var PersonManager $personManager */
                /** @var EntityManager $em */
                /** @var Logger $logger */
                $personManager  = $this->container->get('platypuspie.personmanager');
                $em             = $this->container->get('doctrine.orm.default_entity_manager');
                $logger         = $this->container->get('logger');

                $person = $personManager->getOneBy(array ('email' => $thisUser->getEmail()));

                //todo: move this all into the personManager
                // check if person with email address already exists
                if (!empty($person)) {
                    throw new \Exception ('trying to create person with email address: "'. $thisUser->getEmail() .'" however a person with this email address already exists');
                }

                $logger->info('creating new $person obj as one did not exist for $user object.');

                $person = $personManager->createNew();
                $person->setEmail($thisUser);

                $em->persist($person);
                $em->flush();
            } else {
                throw new \Exception ('User does not have a related person object and could not find email addres from user object to create a new person.');
            }
        }

        return $thisPerson;
    }

    // return true if is in dev environment
    public function isDevEnvironment () {
        if ($this->container->get('kernel')->getEnvironment() == 'dev') {
            return true;
        }

        return false;
    }
}
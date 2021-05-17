<?php

namespace App\VisageFour\Bundle\ToolsBundle\Classes;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Person;
use Doctrine\Common\Persistence\ObjectManager;
use VisageFour\Bundle\ToolsBundle\Services\TerminalColors;
use Twencha\Bundle\EventRegistrationBundle\Services\PersonManager;

abstract class CustomApiTestCase extends ApiTestCase
{
    /** @var PersonManager */
    private $personMan;

    /** @var ObjectManager */
    private $manager;

    static protected $terminalColors;

    protected function setUp(): void
    {
        // this is needed, as tearDown() shuts down the kernel each time.
        // see (for more info): https://stackoverflow.com/questions/59964480/symfony-phpunit-selfkernel-is-null-in-second-test#
        $kernel = self::bootKernel();

        $this->em = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->getServices();

//        $this->outputRedTextToTerminal('hi there!');
//        parent::setUp();
    }

    protected function getServices()
    {
        //        $this->faker = Faker::

        $this->manager          = self::$container->get('doctrine.orm.default_entity_manager');
        $this->personMan        = self::$container->get('twencha.person_man');
    }

    static public function setUpBeforeClass(): void
    {
        // start the symfony kernel
        $kernel = static::createKernel();
        $kernel->boot();

        // get the DI container
        self::$container = $kernel->getContainer();

//        self::$terminalColors = self::$container->get('TerminalColors');
        self::$terminalColors = new TerminalColors();

        return;
    }

    public function outputRedTextToTerminal($msg)
    {
        $text = self::$terminalColors->getColoredString($msg, 'red', 'black');
        print "\n";
        print $text;
    }

    protected function createNewPerson($email, $password): Person
    {
        $person     = $this->personMan->createNewPerson($email,$password);
        $this->manager->persist($person);
        $this->manager->flush();

        return $person;
    }

    protected function removePerson(Person $person)
    {
        $this->manager->remove($person);
        $this->manager->flush();
    }
}
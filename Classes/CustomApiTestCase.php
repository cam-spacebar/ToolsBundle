<?php

namespace App\VisageFour\Bundle\ToolsBundle\Classes;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Services\AppSecurity;
use App\Services\EmailRegisterManager;
use App\Services\FrontendUrl;
use App\Entity\Person;
use App\Services\Factories\PersonFactory;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use VisageFour\Bundle\ToolsBundle\Services\TerminalColors;
use Twencha\Bundle\EventRegistrationBundle\Services\PersonManager;

/**
 * Class CustomApiTestCase
 * @package App\VisageFour\Bundle\ToolsBundle\Classes
 *
 * this class reduces boiler plate such as: creating users, sending requests (finctional testing) setup and tear down etc.
 */
abstract class CustomApiTestCase extends ApiTestCase
{
    /** @var PersonManager */
    private $personMan;

    /** @var ObjectManager */
    protected $manager;

    // the email address of the most recently created user.
    protected $userEmail;

    // the password of the most recently created user.
    protected $userPassword;

    // the target url of the test case. There should only be one as there should only be one test class per endpoint.
    protected $url;

    protected $client;

    protected $debugOutputOn;

    // the current test method running. Useful for displaying in debugging messages.
    private $currentMethod;

    protected $HTTPMethod;

    /**
     * @var Factory
     */
    protected $faker;

    static protected $terminalColors;

    // setup that is specific to the test case that subclasses this class.
    /**
     * @var FrontendUrl
     */
    protected $frontendUrl;
    /**
     * @var PersonFactory
     */
    protected $personFactory;
    /**
     * @var AppSecurity
     */
    protected $appSecurity;

    /**
     * @var Person
     */
    protected $person;

    /**
     * @var EmailRegisterManager
     */
    private $emailRegisterMan;

    abstract protected function specificSetUp ();

    protected function setUp(): void
    {
            // this is needed, as tearDown() shuts down the kernel each time.
        // see (for more info): https://stackoverflow.com/questions/59964480/symfony-phpunit-selfkernel-is-null-in-second-test#
//        $kernel = self::bootKernel();

        $client = static::createClient();
        $client->enableProfiler();

        $this->manager = $client->getKernel()->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->client = $client;

        $this->faker = Factory::create();
        $this->getServices();

//        $this->outputRedTextToTerminal('hi there!');
//        parent::setUp();

//        $this->outputDebugToTerminal('running specificSetUp()');
        $this->specificSetUp();
    }

    protected function getServices()
    {
        // to add a service alias, esarch for marker: #pferfiinw4f
        $this->personMan        = self::$container->get('twencha.person_man');
        $this->frontendUrl      = self::$container->get('test.'. FrontendUrl::class);
        $this->personFactory    = self::$container->get('test.'. PersonFactory::class);
        $this->appSecurity      = self::$container->get('test.'. AppSecurity::class);
        $this->emailRegisterMan = self::$container->get('test.'. EmailRegisterManager::class);
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
//        $this->outputDebugToTerminal("class startASDF\n\n zzz \n\n");

        return;
    }

    /**
     * Sends a "http request" to the $url specified. This just reduces boilerplate in the testcase methods.
     * if $urlOverride is set, it will be the target instead of $this->url. (Note: $this->url is just useful to set in setUp() once, instead of per every test. URL override is used on things like: setup that requires login, as otherwise we'd have to re-set the $this->url from within the test method (not ideal).)
     */
    protected function sendJSONRequest(string $method, $data = null, $urlOverride = null) {
        $json = ['json' => $data];
        if ($method == 'GET') {
            $json = [];
        }

        if (empty($this->url)) {
            throw new \Exception('$this->url cannot be empty. Please set it via: specificSetUp().');
        }

        $url = (empty($urlOverride)) ? $this->url : $urlOverride;
        $crawler = $this->client->request($method, $url, $json);

        return $crawler;
    }

    /**
     * Specifically for debugging outputs. Can be turned on (while developing a single test case) and turned off when doing mass tests.
     */
    protected function outputTestingUrl()
    {
        if(empty($this->url)) {
            throw new \Exception('$this->url cannot be empty. (Error is in test: '. $this->currentMethod .')');
        }

        $this->outputColoredTextToTerminal('testing URL: '. $this->url .' (using HTTP method: '. $this->HTTPMethod .')');

//        $this->outputDebugToTerminal($this->url);
    }

    /**
     * Specifically for debugging outputs. Can be turned on (while developing a single test case) and turned off when doing mass tests.
     */
    protected function setUrl($url)
    {
        $this->url = $url;
        $this->outputTestingUrl();
    }

    /**
     * Specifically for debugging outputs. Can be turned on (while developing a single test case) and turned off when doing mass tests.
     */
    protected function outputDebugToTerminal($msg)
    {
        if ($this->debugOutputOn) {
            $this->outputRedTextToTerminal('DEBUG: '. $msg);
        }
    }

    protected function outputRedTextToTerminal($msg)
    {
        $text = self::$terminalColors->getColoredString($msg, 'red', 'black');
        print "\n";
        print $text;
    }

    protected function outputColoredTextToTerminal($msg, $fgColor = 'red', $bgColor = 'black')
    {
        $msg = 'DEBUG: '.$msg;
        $text = self::$terminalColors->getColoredString($msg, $fgColor, $bgColor);
        print "\n";
        print $text;
    }

    /**
     * @param $email
     * @param $password
     * @return Person
     * @throws \Doctrine\ORM\ORMException
     *
     * Create a new person and store the password and email address (for later access - this is cleaner than statically writing both in the sub class.)
     */
    protected function createNewUser(): Person
    {
        $this->userEmail        = $this->faker->email();
//        $this->userPassword     = null;     // this is the correct flow for registration (as password is set after verification!)

        $this->outputDebugToTerminal(
            'creating person with email: '. $this->userEmail .' and password: '. $this->userPassword
        );

        $person = $this->personMan->createNewPerson($this->userEmail);
        $person->setIsRegistered(true);

        $this->manager->persist($person);
        $this->manager->flush();

        return $person;
    }

    /**
     * @throws \Exception
     *
     * Create a user, complete their registration, verify the account, set the password and also: log them in.
     */
    protected function createUserAndLogin()
    {
        $this->userPassword = $this->faker->password(8);
        $this->person = $this->personFactory->fixturesCreateUserThenRegisterAndVerifyAccount($this->userPassword);

        // login the new user
        $this->setUrl($this->frontendUrl->getSymfonyURL(FrontendUrl::LOGIN));
        $data = [
            'email'         => $this->person->getEmail(),
            'password'      => $this->userPassword
        ];

        $crawler = $this->sendJSONRequest('POST', $data);
//        dump("User login attempt result (#234fwef): \n". $crawler->getContent());

        return $this->person;
    }

    protected function removeUser (Person $person) {
        // removed, because it generates an erro when there's a second API call - yet to find out why.
        // error is: Doctrine\ORM\ORMInvalidArgumentException: Detached entity 1113 cannot be removed
//        $this->manager->merge($person);
//        $this->manager->remove($person);
        $this->manager->flush();
    }

    protected function setShowDebugging(bool $bool) {
        $this->debugOutputOn = $bool;       // turn on when working on a single test case. Turn off when mass executing tests.
        $status = ($bool) ? 'on' : 'off';
        $this->outputRedTextToTerminal('' );
        $this->outputRedTextToTerminal('DEBUGGING is turned: '. $status );
    }

    /**
     * @param string $method
     *
     * e.g. the method name!
     * the debugger uses this to output which method is being executed.
     */
    protected function setCurrentMethod (string $method) {
        $this->currentMethod = $method;

        // remove namespace component of $method
        $offset = strrpos($method, '\\') +1;
        $shortened = substr($method, $offset);

        $this->outputColoredTextToTerminal('Currently in test: '. $shortened .'()', 'blue');

//        zself::$terminalColors->getColoredString($msg, 'red', 'black');
    }
}
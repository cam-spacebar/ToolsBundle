<?php

namespace VisageFour\Bundle\ToolsBundle\Classes;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Exceptions\BadgeAlreadyInPipelineException;
use App\Twencha\Bundle\EventRegistrationBundle\Exceptions\ApiErrorCode;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\RedirectionException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use VisageFour\Bundle\ToolsBundle\Services\PasswordManager;
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
 * @package VisageFour\Bundle\ToolsBundle\Classes
 *
 * this class reduces boiler plate such as: creating users, sending requests (finctional testing) setup and tear down etc.
 *
 * Testing framework documentation:
 * https://docs.google.com/presentation/d/1tAEVY-Ypdv1ClBrCzfk3EqI2QK_wBxd80isKieJDRyw/edit
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
    
    /**
     * @var string
     */
    private $routePairConstant;
    private $urlParams;

    /**
     * @var ApiErrorCode
     * The response from calling $this->client->request() must contain the correct 'body-code'. It also provides the expected HTTP status code which is used for an assert
     * Note; this is *not* HTTP status code, it is the 'body-code' that corresponds to a 'ApiErrorCode' constant.s
     */
    protected $expectedResponse;

    abstract protected function specificSetUp ();

    /**
     * @param $bodyCode
     * for more info no what a body-code is, see the ApiErrorCode class
     */
    protected function setExpectedResponse($bodyCode)
    {
        $this->expectedResponse = new ApiErrorCode($bodyCode);
    }

    /**
     * @param string $routePair
     * @param $data
     * @throws \Exception
     *
     * Generate the target testing URL, and set the route pair (so the test can display a debug msg of what controller and what RoutePair was used - later on).
     */
    protected function setTargetRoutePairConstant (string $routePairConstant, $urlParams = []) {
//        $this->outputDebugToTerminal($this->frontendUrl->getControllerName($routePairConstant));
//        die($routePairConstant .'12eqwds');
        $this->routePairConstant = $routePairConstant;
        $this->urlParams = $urlParams;
//            $this->frontendUrl->getControllerName($routePairConstant);
        $this->setUrl($this->frontendUrl->getSymfonyURL($routePairConstant, $urlParams));
//        $this->setTargetRoutePairConstant(FrontendUrl::CHANGE_PASSWORD);
//        $this->controllerName
    }

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

        return;
    }

    protected function getExpectedBodyCode()
    {
        $this->checkExpectedResponseIsSet();

        return $this->expectedResponse->getBodyCode();
    }

    protected function getExpectedHTTPStatusCode()
    {
        $this->checkExpectedResponseIsSet();

        return $this->expectedResponse->getHTTPStatusCode();
    }

    private function checkExpectedResponseIsSet()
    {
        if (empty($this->expectedResponse)) {
            throw new \Exception ('expectedResponse was not set! Please ensure this is set prior to calling sendJSONRequest(). use setExpectedResponse() with the bodyCode (from class: ApiErrorCode) that is expected. ');
        }
    }

    protected function assetBodyCodesIsAsExpected(ResponseInterface $crawler)
    {
        try {
            $this->assertJsonContains([
                'status' => $this->getExpectedBodyCode()
            ]);
        } catch (ExpectationFailedException $e) {
//        } catch (ClientException|ServerException|RedirectionException $e) {
//            dump($e);
//            $e
            $this->displayResponse($crawler);
        }

    }

    /**
     * Display the http status code and response body.
     */
    protected function displayResponse(ResponseInterface $crawler)
    {
        $data = $crawler->toArray(false);

        dump(
            '',
            'there was a problem. The response body is provided below: ',

            'expected body-code: '. $this->getExpectedBodyCode(),
            'expected HTTP status code: '. $this->getExpectedHTTPStatusCode() .', actual code: '. $crawler->getStatusCode(),
            "error occured in test: ". $this->currentMethod .' (with target URL: '. $this->url .')',
            ' '
        );
//        dump($data);

        // Display stack trace (if one was provided).
        if (isset($data['trace'])) {
            // display stack trace (if it's available)
            $trace = $data['trace'];
            print "\n== Error == \n";
            print $data['hydra:description'] ."\n\n";
            print "== Stack trace ==\n";
            foreach ($trace as $i => $curCall) {
                print "$i: ". $curCall['file'] .' line: '. $curCall['line'] ."\n";
            }
        } else {
            dump('Note: no stack trace provided.');
        }
    }

    /**
     * compare the HTTP status code from the $this->>expectedResponse (ApiErrorCode) with the crawlers HTTP Status code.
     */
    protected function assertHTTPStatusCodeIsAsExpected(ResponseInterface $crawler)
    {
        $expectedHTTPStatusCode = $this->getExpectedHTTPStatusCode();

        $this->assertEquals($expectedHTTPStatusCode, $crawler->getStatusCode());

        // todo: if they dont match, then output the stack trace / error!

        if ($expectedHTTPStatusCode != $crawler->getStatusCode()) {
            print "Status Codes match: Expected: ". $expectedHTTPStatusCode .' == $crawler: '. $crawler->getStatusCode();
        }

    }

    /**
     * Sends a "http request" to the $url specified. This just reduces boilerplate in the testcase methods.
     * if $urlOverride is set, it will be the target instead of $this->url. (Note: $this->url is just useful to set in setUp() once, instead of per every test. URL override is used on things like: setup that requires login, as otherwise we'd have to re-set the $this->url from within the test method (not ideal).)
     */
    protected function sendJSONRequest(string $method, $body = null, $urlOverride = null) {
        $json = [];
        if (!empty($body)) {
            $json = ['json' => $body];
        }

        if ($method == 'GET') {
            $json = [];
        }

        if (empty($this->url)) {
            throw new \Exception('$this->url cannot be empty. Please set it via: specificSetUp().');
        }

        $url = (empty($urlOverride)) ? $this->url : $urlOverride;
        $urlPart = $this->frontendUrl->getSymfonyURL($this->routePairConstant, $this->urlParams, UrlGeneratorInterface::RELATIVE_PATH);
        $msg = 'requesting url: '. $this->url;
        $this->outputColoredTextToTerminal($msg, 'yellow');

        $displayPayload = false;
        $this->outputPayload($displayPayload, $url, $json);

        // run request
        try {
            $crawler = $this->client->request($method, $url, $json);

            $this->assetBodyCodesIsAsExpected($crawler);
            $this->assertHTTPStatusCodeIsAsExpected($crawler);
        } catch (\Exception $e) {
//            dump('Exception during login attempt: (#23fwesd): ', $e);
            die ("\nan error occured during test: ". $this->currentMethod .". Please investigate.\n");
        }

        return $crawler;
    }

    /**
     * Display the payload sent to the url (and the url address)
     */
    private function outputPayload($displayPayload, $url, $json)
    {
        if ($displayPayload) {
            if (!empty($json)) {
                $payload = (!empty($json['json'])) ? $json['json'] : 'empty';

                dump('url called: '. $url .' -- with payload:', $payload);
            } else {
                dump('no payload sent (with request)');
            }

        }
    }

    /**
     * Specifically for debugging outputs. Can be turned on (while developing a single test case) and turned off when doing mass tests.
     */
    protected function outputTestingUrl()
    {
        if(empty($this->url)) {
            throw new \Exception('$this->url cannot be empty. (Error is in test: '. $this->currentMethod .')');
        }
        $msg = 'target URL set to: '. $this->url
            .' '. $this->frontendUrl->getRoutePairDebugMsg($this->routePairConstant)
            .' (using HTTP method: '. $this->HTTPMethod .')'
        ;
        
        $this->outputColoredTextToTerminal($msg);

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
     * Create a (verfied & registered) user, set the password and then: log them in.
     * (note: this is just to prepare for a test case that requires a logged in user).
     */
    protected function createUserAndLogin()
    {
        $this->userPassword = $this->faker->password(PasswordManager::MINIMUM_PASSWORD_LENGTH);
        $this->person = $this->personFactory->fixturesCreateRegisteredUser(true, $this->userPassword);

        // remove the success messages created from successful verification and password change.
        $this->appSecurity->clearFlashes();


        // login the new user
//        $this->setUrl($this->frontendUrl->getSymfonyURL(FrontendUrl::LOGIN));
        $data = [
            'email'         => $this->person->getEmail(),
            'password'      => $this->userPassword
        ];
        $this->setTargetRoutePairConstant(FrontendUrl::LOGIN);
        $this->setExpectedResponse(ApiErrorCode::OK);
        $crawler = $this->sendJSONRequest('POST', $data);
        try {
//            print 'HTTP status code provided: '. $crawler->getStatusCode() ."\n";
            $crawler->getContent(true);
        } catch (ClientException|ServerException|RedirectionException $e) {
            print "\nERROR: error occured while attempting to login a test user. Error msg (generated when calling \$crawler->getContent()): ". $e->getMessage();
            $responseObj = $e->getResponse()->toArray(false);
            dump($responseObj);

            die("\n\ndie() - please fix this problem.\n");
//            dump($e);
        } catch (\Exception $e) {
            die ('an error occured during login attempt. Please investigate.');
        }

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
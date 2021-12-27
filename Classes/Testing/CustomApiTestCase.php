<?php

namespace VisageFour\Bundle\ToolsBundle\Classes\Testing;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Exceptions\BadgeAlreadyInPipelineException;
use App\Exceptions\ApiErrorCode;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\RedirectionException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use VisageFour\Bundle\ToolsBundle\Services\Debugging\ConsoleOutput;
use VisageFour\Bundle\ToolsBundle\Services\PasswordManager;
use App\Services\AppSecurity;
use App\Services\EmailRegisterManager;
use App\Services\FrontendUrl;
use App\Entity\Person;
use App\Services\Factories\PersonFactory;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Twencha\Bundle\EventRegistrationBundle\Services\PersonManager;
use VisageFour\Bundle\ToolsBundle\Services\Testing\TestingHelper;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;

/**
 * Class CustomApiTestCase
 * @package VisageFour\Bundle\ToolsBundle\Classes
 *
 * === Test Case Documentation: ===
 * run using:
 * - ./vendor/bin/phpunit
 * - ./vendor/bin/phpunit src/VisageFour/Bundle/ToolsBundle/Tests/xyz-sub-folder/xyz-Test.php
 * - ./vendor/bin/phpunit --filter xyz-methodName ClassName path/to/file.php
 *
 * Create new test case [P-CB-087]
 * https://docs.google.com/presentation/d/1-AYb7xtRScoWsB3jxHnThsBJVgwY8DzEKLacGVCB28c/edit#slide=id.p
 *
 * (comment version: 1.01)
 */

abstract class CustomApiTestCase extends ApiTestCase
{
    /** @var PersonManager */
    private $personMan;

    /** @var ObjectManager */
    protected $manager;

    /** @var EntityManager */
    protected $em;

    // the email address of the most recently created user.
    protected $userEmail;

    // the password of the most recently created user.
    protected $userPassword;

    // the target url of the test case. There should only be one as there should only be one test class per endpoint.
    protected $url;

    /** @var Client */
    protected $client;

    protected $debugOutputOn;

    // the current test method running. Useful for displaying in debugging messages.
    private $currentMethod;

    /**
     * @var bool
     * when true, the test will test that the (correct / expected) status code is contained within the JSON response.
     * However, this can be turned off - this is done for things like 301 redirects and file downloads, as they do not use json.
     */
    protected $expectStatusCode = true;

    protected $HTTPMethod;

    /**
     * @var Factory
     */
    protected $faker;

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
     * @var ConsoleOutput
     */
    private $consoleOutput;

    /**
     * @var ApiErrorCode
     * The response from calling $this->client->request() must contain the correct 'body-code'. It also provides the expected HTTP status code which is used for an assert
     * Note; this is *not* HTTP status code, it is the 'body-code' that corresponds to a 'ApiErrorCode' constant.s
     */
    protected $expectedResponse;

    /**
     * @var TestingHelper
     */
    protected $testingHelper;

    /**
     * @return string
     */
    public function getRoutePairConstant(): string
    {
        return $this->routePairConstant;
    }

    abstract protected function customSetUp ();

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
     * Generate the target testing URL (including any query parameters), and set the route pair (so the test can display a debug msg of what controller and what RoutePair was used - later on).
     */
    protected function setTargetRoutePairConstant (string $routePairConstant, $urlParams = []) {
//        $this->outputDebugToTerminal($this->frontendUrl->getControllerName($routePairConstant));
//        die($routePairConstant .'12eqwds');
        $this->routePairConstant = $routePairConstant;
        $this->urlParams = $urlParams;
//            $this->frontendUrl->getControllerName($routePairConstant);

        $this->buildUrlWithParams($urlParams);
//        $this->setTargetRoutePairConstant(FrontendUrl::CHANGE_PASSWORD);
//        $this->controllerName
    }

    protected function buildUrlWithParams ($urlParams) {
        $this->setUrl($this->frontendUrl->getSymfonyURL($this->routePairConstant, $urlParams));
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
        $this->em = $this->manager;

        $this->client = $client;

        $this->faker = Factory::create();
        $this->getBaseServices();

//        $this->outputRedTextToTerminal('hi there!');
//        parent::setUp();

//        $this->outputDebugToTerminal('running customSetUp()');
        /** @var consoleOutput */
        $this->consoleOutput = self::$container->get('test.'. ConsoleOutput::class);
        $this->customSetUp();
    }

    protected function getBaseServices()
    {
        // to add a service alias, search for marker: #pferfiinw4f
        $container = self::$container;
        $this->personMan        = $container->get('twencha.person_man');
        $this->frontendUrl      = $container->get('test.'. FrontendUrl::class);
        $this->personFactory    = $container->get('test.'. PersonFactory::class);
        $this->appSecurity      = $container->get('test.'. AppSecurity::class);
        $this->emailRegisterMan = $container->get('test.'. EmailRegisterManager::class);
        $this->testingHelper    = $container->get('test.'. TestingHelper::class);
    }

    static public function setUpBeforeClass(): void
    {
        // start the symfony kernel
        $kernel = static::createKernel();
        $kernel->boot();

        // get the DI container
        self::$container = $kernel->getContainer();

        return;
    }

    protected function getExpectedBodyCode()
    {
        $this->checkExpectedResponseIsSet();

        return $this->expectedResponse->getValue();
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

    // assert that a certain status is returned in the body.
    protected function assetBodyCodesIsAsExpected(ResponseInterface $crawler)
    {
        try {
            $this->assertJsonContains([
                'status' => $this->getExpectedBodyCode()
            ]);
        } catch (ExpectationFailedException $e) {
//            dump('zzz', $e);
            $this->displayErrorNEW($e);
//        } catch (ClientException|ServerException|RedirectionException $e) {
//            dump($e);
//            $e

//            dump($e->getMessage());

            // for some reason, this doesn't work:
//            $this->displayError($e);
//            $this->displayResponse($crawler);
        }

    }

    private function displayErrorNEW(ExpectationFailedException $e)
    {
//        dump('dump($e) below [marker: #dfgerg]: ', $e);
        $comparisonFailure = $e->getComparisonFailure();
//        dump($comparisonFailure);

        dump("== Actual vs. Expected: ==");
        dump('Expected:', $comparisonFailure->getExpected());
        dump('Actual: ', $comparisonFailure->getActual());
        $trace = $e->getTrace();
        // Display stack trace (if one was provided).
        $hideStackTrace = true;
        if ($hideStackTrace) {
            print "\n\nnote: Stack trace hidden (unhide via: [marker: #thr90])";
        } else {
            if (isset($trace)) {
                print "\n\n== Error == \n";
                print $e->getMessage() ."\n\n";


                print "== Stack trace ==\n";
                foreach ($trace as $i => $curCall) {
                    print "$i: ". $curCall['file'] .' line: '. $curCall['line'] ."\n";
                }

//            print "\n\nTip: you can uncomment the dump() of \$e (at: [marker: #dfgerg]) to compare 'actual' to 'expected' to find the issue. \n";

//            dump('Server response: ',$e->get );
            } else {
                print ("\nNote: no stack trace provided.]\n");
            }
        }

    }

    /**
     * Display the http status code and response body.
     */
    protected function displayResponse(ResponseInterface $crawler)
    {
        $data = $crawler->toArray(false);
        $content = json_decode($crawler->getContent(false), true);          // returns assoc array when second param "true"

        if (empty($content->status)) {
            // I'm not sure why it doesn't have a "status" in it, but when no ->status, the response needs to be handled differently
            $this->displayError($content);
            die("--- die() ---\n");
        }

        dump(
            '',
            'there was a problem. The response body is provided below: ',

            'expected body-code: '. $this->getExpectedBodyCode(),
            'actual body-code: '. $content->status .', error msgs: '. $content->error_msgs .' ',
            '',
            'expected HTTP status code: '. $this->getExpectedHTTPStatusCode() .', actual code: '. $crawler->getStatusCode(),
            "error occured in test: ". $this->currentMethod .' (with target URL: '. $this->url .')',
            ' '
        );
//        dump($data);

//        $this->displayError($data['trace']);
    }

    private function displayError($data)
    {
//        dump('123xxx', $data);
        $trace = $data['trace'];
        // Display stack trace (if one was provided).
        if (isset($trace)) {
            print "\n== Error == \n";
            print $data['hydra:description'] ."\n\n";


            print "== Stack trace ==\n";
            foreach ($trace as $i => $curCall) {
                print "$i: ". $curCall['file'] .' line: '. $curCall['line'] ."\n";
            }
        } else {
            print ("\nNote: no stack trace provided.]\n");
        }
    }

    /**
     * compare the HTTP status code from the $this->expectedResponse (ApiErrorCode) with the crawlers HTTP Status code.
     */
    protected function assertHTTPStatusCodeIsAsExpected(ResponseInterface $crawler)
    {
        $expectedHTTPStatusCode = $this->getExpectedHTTPStatusCode();
//        dump($crawler->getStatusCode());
//
        $errorMsg = 'Custom error msg: Expected status code: '. $expectedHTTPStatusCode .', but got: '. $crawler->getStatusCode() .' instead.';
        $this->assertEquals($expectedHTTPStatusCode, $crawler->getStatusCode(), $errorMsg);

        // todo: if they dont match, then output the stack trace / error!

        if ($expectedHTTPStatusCode != $crawler->getStatusCode()) {
            print "Status codes don't match: Expected: ". $expectedHTTPStatusCode .' == $crawler: '. $crawler->getStatusCode();
        }

    }

    /**
     * Sends a "http request" to the $url specified. This just reduces boilerplate in the testcase methods.
     * if $urlOverride is set, it will be the target instead of $this->url. (Note: $this->url is just useful to set in setUp() once, instead of per every test. URL override is used on things like: setup that requires login, as otherwise we'd have to re-set the $this->url from within the test method (not ideal).)
     */
    protected function sendJSONRequest(string $method, $body = null, $urlOverride = null, $displayDebugging = false) {
        $json = [];
        if (!empty($body)) {
            $json = ['json' => $body];
        }

        if ($method == 'GET') {
            $json = [];
        }

        if (empty($this->url)) {
            throw new \Exception('$this->url cannot be empty. Please set it via: customSetUp().');
        }

        // display useful debugging info about a request (optional)
        if ($displayDebugging) {
            dump('payload / body data: ', $body);
        }

        if (!empty($body) && $method == "GET") {
            throw new \Exception(
                'You have a $body set (for your request), but you are using a GET HTTP method for the request. '.
                "(Used when sending a request to controller with the route-pair: ". $this->routePairConstant .")"
//                'You may want to use $this->buildUrlWithParams($params), as this will build the url with the params (instead of trying to send with the request body).'
            );
        }

        $url = (empty($urlOverride)) ? $this->url : $urlOverride;
        $urlPart = $this->frontendUrl->getSymfonyURL($this->routePairConstant, $this->urlParams, UrlGeneratorInterface::RELATIVE_PATH);
        $msg = 'requesting url: '. $this->url;
        $this->outputColoredTextToTerminal($msg, 'orange');

        $displayPayload = false;
        $this->outputPayload($displayPayload, $url, $json);

        // run request
        try {
            $crawler = $this->client->request($method, $url, $json);

            if ($this->expectStatusCode) {
                $this->assetBodyCodesIsAsExpected($crawler);
            }
            $this->assertHTTPStatusCodeIsAsExpected($crawler);
        } catch (\Exception $e) {
//            dump('Exception during login attempt: (#23fwesd): ', $e);
            $this->displayException($e);
//            dump($e);
            die ("\nan error occured during test: ". $this->currentMethod .". Please investigate.\n");
        }

        return $crawler;
    }

    private function displayException (\Exception $e)
    {
        print "\n=== An exception occured ===";
        print "\nException class:" . get_class($e);
        print "\nmessage: ". $e->getMessage();

        print "\n\nStack trace:\n";
        foreach ($e->getTrace() as $curI => $curItem) {
            print "- ". $curItem['file'] .', [line: '. $curItem['line'] ."]\n";
        }
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

        $this->outputColoredTextToTerminal(
            'target URL set to: '. $this->url
        );

        $this->outputColoredTextToTerminal(
            $this->frontendUrl->getRoutePairDebugMsg($this->routePairConstant)
            .' (using HTTP method: '. $this->HTTPMethod .')'
        );
        


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
            $this->consoleOutput->outputRedTextToTerminal($msg);
        }
    }

    protected function outputRedTextToTerminal($msg)
    {
        $this->consoleOutput->outputRedTextToTerminal($msg);
    }

    protected function outputColoredTextToTerminal($msg, $fgColor = 'red', $bgColor = 'black')
    {
        $this->consoleOutput->outputColoredTextToTerminal($msg, '', $fgColor, $bgColor);
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
//            dump($responseObj);

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

    }
}
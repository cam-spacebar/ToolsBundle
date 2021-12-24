<?php

namespace VisageFour\Bundle\ToolsBundle\Tests\UserSystem;

use App\Services\FrontendUrl;
use App\Exceptions\ApiErrorCode;
use VisageFour\Bundle\ToolsBundle\Classes\ApiStatusCode\VFApiStatusCodes;
use VisageFour\Bundle\ToolsBundle\Classes\Testing\CustomApiTestCase;

/**
 * Class SecurityTest
 * @package App\Tests\Service
 *
 * run using:
 * - ./vendor/bin/phpunit
 * - ./vendor/bin/phpunit src/VisageFour/Bundle/ToolsBundle/Tests/UserSystem/VerifyAccountTokenTest.php
 * - ./vendor/bin/phpunit src/VisageFour/Bundle/ToolsBundle/Tests/UserSystem/VerifyAccountTokenTest.php --filter verifyAccountWithCorrectToken
 *
 * Testing framework documentation:
 * https://docs.google.com/presentation/d/1tAEVY-Ypdv1ClBrCzfk3EqI2QK_wBxd80isKieJDRyw/edit
 */

// This tests controller: verifyEmailAccountViaTokenAction()
class VerifyAccountTokenTest extends CustomApiTestCase
{
    /**
     * Setup that is specific to this test case.
     * (this runs once prior to each test case / method())
     */
    protected function customSetUp()
    {
        $this->setShowDebugging(true);
        $this->outputDebugToTerminal('running customSetUp()');


        $this->person = $this->createNewUser();
        $this->person->setIsRegistered(true);
    }

    protected function tearDown(): void
    {
        $this->outputDebugToTerminal('tearDown()');
        $this->removeUser($this->person);
    }

    /**
     * @test
     * ./vendor/bin/phpunit src/VisageFour/Bundle/ToolsBundle/Tests/UserSystem/VerifyAccountTokenTest.php --filter verifyAccountWithCorrectToken
     */
    public function verifyEmailWithIncorrectToken(): void
    {
        $this->setCurrentMethod(__METHOD__);
        $data = [
            'email' => $this->userEmail,
            'verificationToken' => 'bad token'
        ];
        $this->setTargetRoutePairConstant(FrontendUrl::CONFIRM_EMAIL, $data);
        $this->setExpectedResponse(VFApiStatusCodes::INVALID_ACCOUNT_VERIFICATION_TOKEN);
//        $this->buildUrlWithParams($data);
        $crawler = $this->sendJSONRequest('GET');
//        dump($crawler->getcontent());

        $this->assertResponseHeaderSame('content-type', 'application/json');
//        $this->assertResponseIsSuccessful();
//        $this->assertSelectorTextContains('h1', 'Hello World');./vendor/bin/phpunit --colors
//        $this->assertEquals(42, 42);

    }

    /**
     * @test
     */
    public function verifyAccountWithCorrectToken(): void
    {
        $this->setCurrentMethod(__METHOD__);

        // Test with a correct token
        $correctToken = $this->person->getVerificationToken();

        $data = [
            'email'             => $this->userEmail,
            'verificationToken' => $correctToken
        ];
        $this->setTargetRoutePairConstant(FrontendUrl::CONFIRM_EMAIL, $data);
        $this->setExpectedResponse(VFApiStatusCodes::OK);
        $crawler = $this->sendJSONRequest('GET');
//        dump($crawler->getcontent());

        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    /**
     * @test
     */
    public function verifyAccountTwiceError(): void
    {
        $this->setCurrentMethod(__METHOD__);
        // attempt verification (first)
        $correctToken = $this->person->getVerificationToken();
        $data = [
            'email' => $this->userEmail,
            'verificationToken' => $correctToken
        ];
        $this->setTargetRoutePairConstant(FrontendUrl::CONFIRM_EMAIL, $data);
        $this->setExpectedResponse(VFApiStatusCodes::OK);
        $crawler = $this->sendJSONRequest('GET');
//        dump($crawler->getcontent());

        $this->manager->flush();

        // attempt validation (second time)
        $this->setExpectedResponse(VFApiStatusCodes::ACCOUNT_ALREADY_VERIFIED);
//        $this->buildUrlWithParams($data);
        $crawler = $this->sendJSONRequest('GET');
        $this->assertResponseHeaderSame('content-type', 'application/json');
//        $this->removeUser($this->person);
    }
}
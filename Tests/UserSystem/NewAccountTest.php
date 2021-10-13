<?php

namespace VisageFour\ToolsBundle\Tests\UserSystem;

use App\Services\FrontendUrl;
use App\Exceptions\ApiErrorCode;
use VisageFour\Bundle\ToolsBundle\Classes\CustomApiTestCase;
use VisageFour\Bundle\ToolsBundle\Services\PasswordManager;

/**
 * Class SecurityTest
 * @package App\Tests\Service
 *
 * run using:
 * - ./vendor/bin/phpunit
 * - ./vendor/bin/phpunit src/VisageFour/Bundle/ToolsBundle/Tests/UserSystem/NewAccountTest.php
 * - ./vendor/bin/phpunit --filter changePasswordCorrectly ClassName path/to/file.php
 *
 * Testing framework documentation:
 * https://docs.google.com/presentation/d/1tAEVY-Ypdv1ClBrCzfk3EqI2QK_wBxd80isKieJDRyw/edit
 */

// controller method (being tested): changePasswordAction()
class NewAccountTest extends CustomApiTestCase
{

    /**
     * Setup that is specific to this test case.
     * (this runs once prior to each test case / method())
     */
    protected function specificSetUp()
    {
        $this->setShowDebugging(true);
//        $this->outputDebugToTerminal('running specificSetUp()');

        $this->HTTPMethod = 'POST';
//        $this->setTargetRoutePairConstant(FrontendUrl::RESET_PASSWORD);
//
//        $this->person = $this->createNewUser();
//        $this->person->createChangePasswordToken();
//        $this->person->setAccountIsVerified(true);
//        $this->person->createChangePasswordToken();
//        $this->manager->persist($this->person);

    }

    protected function tearDown(): void
    {
//        $this->outputDebugToTerminal('tearDown()');
//        $this->removeUser($this->person);
//        $this->manager->persist($this->person);
//        $this->manager->flush();
    }



    /**
     * @test
     * ./vendor/bin/phpunit --filter createNewAccountSuccessfully
     */
    public function createNewAccountSuccessfully(): void
    {
        $this->setCurrentMethod(__METHOD__);

        // create a new account
        $this->setTargetRoutePairConstant(FrontendUrl::NEW_ACCOUNT);
        $this->setExpectedResponse(ApiErrorCode::OK);
        $data = [
            'email'         => $this->faker->email()
        ];
        $crawler = $this->sendJSONRequest('POST', $data);
        $this->buildUrlWithParams($data);
//        $this->outputDebugToTerminal('email: '. $this->person->getPassword());
        $this->setExpectedResponse(ApiErrorCode::OK);
        $this->assertResponseHeaderSame('content-type', 'application/json');

        // send a duplicate create account request

        $this->manager->flush();
        $crawler = $this->sendJSONRequest('POST', $data);
    }

}
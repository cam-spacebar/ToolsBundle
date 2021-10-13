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
 * - ./vendor/bin/phpunit src/VisageFour/Bundle/ToolsBundle/Tests/UserSystem/ChangePasswordTest.php
 * - ./vendor/bin/phpunit --filter changePasswordCorrectly ClassName path/to/file.php
 *
 * Testing framework documentation:
 * https://docs.google.com/presentation/d/1tAEVY-Ypdv1ClBrCzfk3EqI2QK_wBxd80isKieJDRyw/edit
 */

// controller method (being tested): changePasswordAction()
class ChangePasswordTest extends CustomApiTestCase
{

    /**
     * Setup that is specific to this test case.
     * (this runs once prior to each test case / method())
     */
    protected function specificSetUp()
    {
        $this->setShowDebugging(true);
//        $this->outputDebugToTerminal('running specificSetUp()');

        $this->HTTPMethod = 'GET';
        $this->setTargetRoutePairConstant(FrontendUrl::RESET_PASSWORD);

        $this->person = $this->createNewUser();
        $this->person->createChangePasswordToken();
        $this->person->setAccountIsVerified(true);
        $this->person->createChangePasswordToken();
        $this->manager->persist($this->person);

    }

    protected function tearDown(): void
    {
//        $this->outputDebugToTerminal('tearDown()');
        $this->removeUser($this->person);
        $this->manager->persist($this->person);
        $this->manager->flush();
    }

    /**
     * @test
     * ./vendor/bin/phpunit --filter changePasswordCorrectly
     */
    public function changePasswordCorrectly(): void
    {
        $this->setCurrentMethod(__METHOD__);
        $newPassword = $this->faker->password (8);
        $this->outputDebugToTerminal('(encoded) default password: '. $this->person->getPassword());
        $this->outputDebugToTerminal('new (unencoded) password: '. $newPassword);
//dump('tokenzzz:', $this->person->getChangePasswordToken());
        $urlParams = [
            'changePasswordToken'   => $this->person->getChangePasswordToken(),
            'newPassword'           => $newPassword,
            'email'                 => $this->person->getEmail()
        ];
        $this->buildUrlWithParams($urlParams);
        $this->setExpectedResponse(ApiErrorCode::OK);
        $crawler = $this->sendJSONRequest('GET');
//        dump($crawler);
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $this->manager->flush();

        // Attempt login with new (correct) password.
        $this->setTargetRoutePairConstant(FrontendUrl::LOGIN);
        $this->setExpectedResponse(ApiErrorCode::OK);
        $data = [
            'email'         => $this->person->getEmail(),
            'password'      => $newPassword
        ];
        $crawler = $this->sendJSONRequest('POST', $data);
//        dump($crawler->getcontent());
        $this->buildUrlWithParams($data);
        $this->outputDebugToTerminal('password: '. $this->person->getPassword());
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $this->manager->flush();
    }

    /**
     * @test
     * ./vendor/bin/phpunit --filter changePasswordUsingInvalidPassword
     */
    public function changePasswordUsingInvalidPassword(): void
    {
        $this->setCurrentMethod(__METHOD__);
        $newPassword = 'toshort';
        $this->outputDebugToTerminal('current (encoded) password: '. $this->person->getPassword());
        $this->outputDebugToTerminal('new password: '. $newPassword);
        $data = [
            'changePasswordToken' => $this->person->getChangePasswordToken(),
            'newPassword'   => $newPassword,
            'email'         => $this->person->getEmail()
        ];
        $this->buildUrlWithParams($data);
        $this->setExpectedResponse(ApiErrorCode::INVALID_NEW_PASSWORD);
        $crawler = $this->sendJSONRequest('GET');
//        dump($crawler->getcontent());

        $this->assertResponseHeaderSame('content-type', 'application/json');

    }

    /**
     * @test
     */
    public function changePasswordWhenUserAccountNotVerified(): void
    {
        $this->setCurrentMethod(__METHOD__);
        $this->person->setAccountIsVerified(false);

        $this->outputDebugToTerminal('password: '. $this->person->getPassword());

        $data = [
            'changePasswordToken' => $this->person->getChangePasswordToken(),
            'newPassword'   => $this->faker->password(PasswordManager::MINIMUM_PASSWORD_LENGTH),
            'email'         => $this->person->getEmail()
        ];
        $this->buildUrlWithParams($data);
        $this->setExpectedResponse(ApiErrorCode::ACCOUNT_NOT_VERIFIED);
        $crawler = $this->sendJSONRequest('GET');
//        dump($crawler->getcontent());
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

}
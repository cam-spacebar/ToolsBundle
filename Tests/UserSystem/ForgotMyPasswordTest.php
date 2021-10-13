<?php

namespace VisageFour\ToolsBundle\Tests\UserSystem;

use App\Services\FrontendUrl;
use App\Entity\Person;
use App\Exceptions\ApiErrorCode;
use VisageFour\Bundle\ToolsBundle\Classes\CustomApiTestCase;
use VisageFour\Bundle\ToolsBundle\Services\BaseFrontendUrl;
use VisageFour\Bundle\ToolsBundle\Services\PasswordManager;

/**
 *
 * Run using:
 * - ./vendor/bin/phpunit
 * - ./vendor/bin/phpunit src/VisageFour/Bundle/ToolsBundle/Tests/UserSystem/ForgotMyPasswordTest.php
 * - ./vendor/bin/phpunit --filter changePasswordCorrectly ClassName path/to/file.php
 *
 * Testing framework documentation:
 * https://docs.google.com/presentation/d/1tAEVY-Ypdv1ClBrCzfk3EqI2QK_wBxd80isKieJDRyw/edit
 */

// controller method (being tested): resetPasswordAction()
class ForgotMyPasswordTest extends CustomApiTestCase
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
        $this->setTargetRoutePairConstant(BaseFrontendUrl::FORGOT_YOUR_PASSWORD);

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
     * ./vendor/bin/phpunit --filter forgotMyPasswordRequest
     */
    public function forgotMyPasswordRequest(): void
    {
        $this->setCurrentMethod(__METHOD__);

        $data = [
            'email'         => $this->person->getEmail()
        ];
        $this->setExpectedResponse(ApiErrorCode::OK);
        $crawler = $this->sendJSONRequest('POST', $data);
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }
}
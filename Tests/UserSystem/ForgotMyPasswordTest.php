<?php

namespace VisageFour\Bundle\ToolsBundle\Tests\UserSystem;

use App\Services\FrontendUrl;
use App\Entity\Person;
use App\Exceptions\ApiErrorCode;
use VisageFour\Bundle\ToolsBundle\Classes\ApiStatusCode\VFApiStatusCodes;
use VisageFour\Bundle\ToolsBundle\Classes\Testing\CustomApiTestCase;
use VisageFour\Bundle\ToolsBundle\Services\BaseFrontendUrl;
use VisageFour\Bundle\ToolsBundle\Services\PasswordManager;

/**
 *
 * === Test Case Documentation: ===
 * run using:
 * - ./vendor/bin/phpunit
 * - ./vendor/bin/phpunit src/VisageFour/Bundle/ToolsBundle/Tests/UserSystem/ForgotMyPasswordTest.php
 * - ./vendor/bin/phpunit --filter changePasswordCorrectly ClassName path/to/file.php
 *
 * Create new test case [P-CB-087]
 * https://docs.google.com/presentation/d/1-AYb7xtRScoWsB3jxHnThsBJVgwY8DzEKLacGVCB28c/edit#slide=id.p
 *
 * (comment version: 1.01)
 */


// controller method (being tested): resetPasswordAction()
class ForgotMyPasswordTest extends CustomApiTestCase
{

    /**
     * Setup that is specific to this test case.
     * (this runs once prior to each test case / method())
     */
    protected function customSetUp()
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
        $this->setExpectedResponse(VFApiStatusCodes::OK);
        $crawler = $this->sendJSONRequest('POST', $data);
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }
}
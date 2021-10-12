<?php
/**
 * User: CameronBurns
 * Date: 4/05/2021
 * Time: 2:07 PM
 */

namespace VisageFour\Bundle\ToolsBundle\Controller;

use App\Services\AppSecurity;
use App\Services\FrontendUrl;
use VisageFour\Bundle\ToolsBundle\Services\ResponseAssembler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use VisageFour\Bundle\ToolsBundle\ClassExtension\CustomController;
use Twencha\Bundle\EventRegistrationBundle\Services\RegistrationManager;
use VisageFour\Bundle\ToolsBundle\Interfaces\ApiErrorCodeInterface;

// use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

// note: no longer used by Anchorcards, so can be modified as needed.
class BaseSecurityController extends CustomController
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorageInterface;

    /**
     * @var AppSecurity
     */
    private $appSecurity;

    public function __construct (AppSecurity $appSecurity, TokenStorageInterface $tokenStorageInterface, ResponseAssembler $ra)
    {
        parent::__construct($ra, $appSecurity);
        $this->tokenStorageInterface = $tokenStorageInterface;
        $this->appSecurity = $appSecurity;
    }

    /**
     * @Route("/registerNew", name="app_registration", methods={"POST"})
     * @param Request $request
     */
    public function registerNewUserAction(Request $request, RegistrationManager $regMan)
    {
        $this->appSecurity->registerNewUser();
    }

    /**
     * @Route("/login", name="app_login_get", methods={"GET"})
     *
     * Instructs the front-end to redirect to the /login page (that should use POST)
     */
    public function loginGETAction(AuthenticationUtils $authenticationUtils): JsonResponse
    {
        return $this->assembleJsonResponse(null, FrontendUrl::LOGIN);
    }

    /**
     * @Route("/loginForm", name="app_login_post", methods={"POST"})
     *
     */
    public function loginPOSTAction(Request $request): JsonResponse
    {
        throw new \Exception('this is hijacked by the authenticator class named: LoginFormAuthenticator. see: onAuthenticationSuccess() or onAuthenticationFailure().');
//        return $this->appSecurity->loginUserViaPOST($request);

//        $logger->info('request', $request);
////        die('die in loginPOSTAction()');
//
//        // get the login error if there is one
//        $error = $authenticationUtils->getLastAuthenticationError();
//
//        // last username entered by the user
//        $lastEmail = $authenticationUtils->getLastUsername();
//
//        // todo: add csrf protection for react form
////        if ($this->has('security.csrf.token_manager')) {
////            $csrfToken = $this->get('security.csrf.token_manager')->getToken('authenticate')->getValue();
////        } else {
////            // BC for SF < 2.4
////            $csrfToken = $this->has('form.csrf_provider')
////                ? $this->get('form.csrf_provider')->generateCsrfToken('authenticate')
////                : null;
////        }
//
//        return $this->assembleJsonResponse($request);
    }

    /**
     * @Route("/logout1", name="app_logout", methods={"GET"})
     *
     * Don't user this for API, it 302 redirects instead of returning JSON. use: manual_logout instead.
     */
    public function logoutAction()
    {
        throw new \Exception('this should not be displayed - as its the logout controller.');
    }

    /**
     * @Route("/logout", name="app_manual_logout", methods={"GET"})
     *
     * Logs out and returns JSON (without sending a 302 redirect)
     */
    public function manualLogoutAction (Request $request): JsonResponse {
        return $this->appSecurity->logoutUser($request);
//        return $this->redirect($this->generateUrl('login'));
    }

    /**
     * @Route("/confirm_email/{email}/{verificationToken}", name="confirm_email_get", methods={"GET"})
     *
     * Verify the email registration link provided (ussually sent to the user via email upon registration.)
     *
     * test case class: VerifyTokenTest
     */
    public function verifyEmailAccountViaTokenAction ($email, $verificationToken) {
        return $this->appSecurity->verifyEmailAccountViaToken($email, $verificationToken);
    }

    /**
     * @Route("/confirm_email/{email}/{verificationToken}", name="confirm_email_post", methods={"POST"})
     */
    public function verifyEmailAccountViaTokenPOSTAction () {
        throw new \Exception('you must use the GET HTTP method as POST can not be achieved via the link sent to someones email.');
    }

    /**
     * @Route("/reset_password", name="reset_password", methods={"GET"})
     *
     * This controller is tested via method: resetPasswordTest()
     * (note: must be GET, as link is sent via email)
     */
    public function handleResetPasswordRequestAction(Request $request): JsonResponse
    {
        return $this->appSecurity->handleResetPasswordRequest($request);
    }

    /**
     * @Route("/forgot_your_password", name="forgot_your_password", methods={"POST"})
     *
     * This controller is tested via method: ??()
     */
    public function forgotMyPasswordAction(Request $request, ResponseAssembler $ra, AppSecurity $appSecurity): JsonResponse
    {

        try {
            $email = $appSecurity->getPOSTParam($request,'email');
            return $appSecurity->processForgotMyPasswordRequest($email);

        } catch (ApiErrorCodeInterface $e) {

            return $ra->assembleJsonResponse(null, $e->getRedirectionCode(), $e);
        }
    }
}
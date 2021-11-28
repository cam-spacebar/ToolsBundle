<?php

namespace VisageFour\Bundle\ToolsBundle\Services;

use App\Controller\AdminMenuController;
use App\Controller\SecurityController;
use VisageFour\Bundle\ToolsBundle\Controller\UrlShortener\LandingPageController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use VisageFour\Bundle\ToolsBundle\Interfaces\FrontendUrlInterface;

/**
 * This provides a list of front-end urls and an additional mapping to the backend (symfony) route.
 * This should allow for easy (centralized) changes to route names and urls
 *
 * note: front-end "file path" url part and backend URL should match (to keep it simple),
 * however symfony route_name does not need to match.
 *
 * == Implementation code ==:
 * FrontendUrl::getSymfonyRouteName(FrontendUrl::MAIN_LOGGED_IN_USER_MENU)
 */
class BaseFrontendUrl implements FrontendUrlInterface
{
    /**
     * @var array
     * a list of all possible routes - with a route to the front-end and another to the backend.
     */
    protected $routePairList = [];

    // this ensures that a redirection is *specifically* set, so that if a null / false is accidentally returned, that the bug is caught.
    public const NO_REDIRECTION = 'noRedirect';

    private $baseUrl;

    // list of routes constants:
    const LOGIN                     = 'LOGIN';
    const CONFIRM_EMAIL             = 'CONFIRM_EMAIL';
    const MAIN_LOGGED_IN_USER_MENU  = 'MAIN_LOGGED_IN_USER_MENU';
    const LOGOUT                    = 'LOGOUT';
    const FORGOT_YOUR_PASSWORD      = 'FORGOT_YOUR_PASSWORD';
    const RESET_PASSWORD            = 'RESET_PASSWORD';
    const USER_REGISTRATION         = 'USER_REGISTRATION';
    const ACCOUNT_VERIFICATION      = 'ACCOUNT_VERIFICATION';
    const NEW_ACCOUNT               = 'NEW_ACCOUNT';
    const SHORTENED_URL_LP          = 'SHORTENED_URL_LP';

    const NO_FRONTEND = 'NO_FRONTEND';      // placeholder to indicate that there's no "front-end", maybe because the "front-end" is acctually delivered via the backend as HTML (not a react client)

    // in this case, use GET ow new MissingInputException();n the symfony route_name to get the page.

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * @var string
     */
    private $frontend_base_url;

    public function __construct (UrlGeneratorInterface $router, string $frontend_base_url)
    {
        // todo: add an environment var to determine if is in prod! (then change to www.newtoMelbourne.org

        $env = 'test';
        if ($env == 'prod') {
            // todo: create a domain variable.
            $this->baseUrl = 'http://api.newtomelbourne.org';
        } else {
            // test and dev:
            $this->baseUrl = 'http://localhost:8000';
        }

        $this->router               = $router;
        $this->frontend_base_url    = $frontend_base_url;
        $this->populateRouteList();
    }

    /**
     * return a string like:
     * (controller: PaymentsController::OrderNewBadgeAction(), route name: change_password)
     * many used to provide additional debug information for things like testing.
     */
    public function getRoutePairDebugMsg($routePairConstant, $forBackend = true): string
    {
        if (empty($routePairConstant)){
            throw new \Exception('$routePairConstant is empty, it must be set.');
        }
        if ($forBackend) {
            // display symfony route
            return ('(controller: '.
                $this->getControllerName($routePairConstant) .
                ", route_name: ".
                $this->getSymfonyRouteNAME($routePairConstant)
                .')'
            );
        } else {
            // display front-end route details
            throw new \Exception('not implemented yet');
        }

    }

    public function checkIfRouteExists ($constant) { // dont add an type to the parameter
        // check if null was sent
        if (empty($constant)) {
            throw new \Exception(
                'A falsey value (likely Null) was provided as the (route-pair) $constant for in the FrontendUrl class.'.
                " You must explicitly set the $constant value to: FrontendUrl::NO_REDIRECTION to prevent redirection (i.e. don't use null)."
            );
        }

        if (!$this->doesRouteConstantExist($constant)) {
            throw new \Exception (
                "a route-pair with the value: $constant has not been configured. (Search for marker: #CMDKKD00 to add new routes)"
            );
        }

//        if (empty($route)) {
//            throw new \Exception ('route_name cannot be empty.');
//        }
    }

    private function doesRouteConstantExist($constant)
    {
        return (!empty($this->routePairList[$constant]));
    }

    /**
     * @param string $constant
     * @return string
     * @throws \Exception
     *
     * Return the URL of the symfony route (for the $constant provided).
     */
    public function getSymfonyURL (string $constant, array $params = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        $routeName = $this->getSymfonyRouteNAME($constant);

        // generate URL
        $pathPart = $this->router->generate($routeName, $params, $referenceType);
        return $this->baseUrl . $pathPart;
    }

    public function getControllerName(string $routePairConstant)
    {
        $this->checkControllerElementExists($routePairConstant);
        return $this->routePairList[$routePairConstant]['controller'];
    }

    private function checkControllerElementExists(string $routePairConstant)
    {
        if (empty($this->routePairList[$routePairConstant]['controller'])) {
            throw new \Exception ('you must set a "controller" value for route-pair constant: '. $routePairConstant .' (goto class: FrontendURL to do this).');
        }

        return true;
    }

    private function checkControllerActuallyExists($routePairConstant)
    {
        $this->checkControllerElementExists($routePairConstant);
//        dump($routePairConstant);

        // use reflection to test for the class
//            $methodName = 'OrderNewBadgeAction';
        $controllerName = $this->getControllerName($routePairConstant);
        $pieces = explode('::', $controllerName);

        // this will throw an error is ReflectionException if the class or method doesn't exist
        $i = new \ReflectionClass($pieces[0]);
        $i->getMethod($pieces[1]);

        return true;

    }

    public function getSymfonyRouteNAME ($constant)
    {
        $this->checkIfRouteExists($constant);
        $routeName = $this->routePairList[$constant]['route_name'];

        if (empty($routeName)) {
            throw new \Exception('the route_name element for route-pair constant: '. $constant .' cannot be empty');
        }

        return $routeName;
    }

    const FORMAT_RELATIVE  = 'RELATIVE';
    const FORMAT_ABSOLUTE  = 'ABSOLUTE';
    public function getFrontendUrl(string $constant, $urlData = [], $format = self::FORMAT_RELATIVE)
    {
        $populatedPath = $this->generateURLPart($constant, $urlData);

        switch ($format) {
            case self::FORMAT_RELATIVE:
                return $populatedPath;
                break;
            case self::FORMAT_ABSOLUTE:
                return $this->frontend_base_url . $populatedPath;
                break;
            default:
                throw new \Exception('this $format is not recognised.');
        }
    }

    /**
     * @param $constant
     * @return mixed
     * @throws \Exception
     *
     * return the 'file path' url part for the front-end
     */
    public function getFrontendURLPart ($constant, $addBaseUrl = false)
    {
        $this->checkIfRouteExists($constant);

        $route = $this->routePairList[$constant];

//        dd($route);
        if (!isset($route['front_end'])) {
            throw new \Exception('The route-constant: "'. $constant.'"["front-end"] was not set. Please fix this.');
        }
        $pathPart = $route['front_end'];

        if ($addBaseUrl == true) {
            return $this->baseUrl .'/'. $pathPart;
        }

        return '/'. $route['front_end'];
    }

    /**
     * @param $constant
     * @param array $data
     *
     * Replace the placeholders with real values from $data.
     *
     * take something like this:
     *   confirm_email/{EMAIL}/{VERIFICATION_TOKEN}
     * and converts it to:
     *   /confirm_email/willie.dickens%40gmail.com/0fb26653e290c67ed7687efa9fa3ea7e
     */
    private function generateURLPart($constant, $data = [])
    {
//        dump($constant);
        // replace the placeholders with their $data values
        $pathPart = $this->getFrontendURLPart($constant);
        $populatedPath = $pathPart;

        foreach($data as $key => $replacementValue) {
            $replacementValue = urlencode($replacementValue);
            $needle = '{'.strtoupper($key).'}';

            if(strpos($populatedPath, $needle) == false) {
                throw new \Exception('Cannot use placeholder named: "'. $needle
                    .'" in URL construction, as it doesn\'t exist in the pathPart: "/'. $pathPart .'"'
                    .' (using route-pair constant: Base/FrontendUrl::'. $constant .')'
                );
            }

            $populatedPath = str_replace($needle, $replacementValue, $populatedPath);
        }

        // check for any remaining placeholders that were not provided a value:
        $leftBracket  = (strpos($populatedPath, '{'));
        if (!empty($leftBracket)) {
            $rightbracket  = (strpos($populatedPath, '}'));
            if (!empty($rightbracket)) {
                $missingPlaceholderName = substr($populatedPath, $leftBracket+1, ($rightbracket - $leftBracket -1));
                dump('placeholder data dump below (#sdcs3): ', $data);
                throw new \Exception(
                    'You must provide a value for the placeholder: "'. $missingPlaceholderName .'" for path-part: '. $pathPart
                    .' (using route-pair constant: Base/FrontendUrl::'. $constant .')'
                );
            }
        }

        return $populatedPath;
    }

    /**
     * @return $this
     * @throws \Exception
     *
     * add a list of routes to the main routeList
     */
    private function populateRouteList()
    {
        // Add new route marker: #ERVE$#
        $routes = [
            self::LOGIN => [
                'controller'        => SecurityController::class .'::loginPOSTAction',          // controller listed here for debugging (finding the controller quickly).
                'route_name'        => 'app_login_post',
                'front_end'         => 'login'
            ],
            self::LOGOUT => [
                'controller'        => SecurityController::class .'::manualLogoutAction',          // controller listed here for debugging (finding the controller quickly).
                'route_name'        => 'app_manual_logout',
                'front_end'         => ''
            ],
            self::CONFIRM_EMAIL => [
                'controller'        => SecurityController::class .'::verifyEmailAccountViaTokenAction',          // controller listed here for debugging (finding the controller quickly).
                'route_name'        => 'confirm_email_get',
                'front_end'         => 'confirm_email?email={EMAIL}&verification_token=reset_password{VERIFICATION_TOKEN}'
            ],
            self::MAIN_LOGGED_IN_USER_MENU => [
                'controller'        => AdminMenuController::class .'::MainLoggedInUserMenuAction',
                'route_name'        => 'main_loggedin_user_menu',
                'front_end'         => 'userMenu'
            ],
            self::FORGOT_YOUR_PASSWORD => [
                'controller'        => SecurityController::class.'::forgotMyPasswordAction',
                'route_name'        => 'forgot_your_password',
                'front_end'         => 'forgot_your_password'
            ],
            self::RESET_PASSWORD => [
                'controller'        => SecurityController::class.'::handleResetPasswordRequestAction',
                'route_name'        => 'reset_password',
                'front_end'         => 'reset_password?email={EMAIL}&changePasswordToken={CHANGEPASSWORDTOKEN}'
            ],
            self::NEW_ACCOUNT => [
                'controller'        => SecurityController::class .'::registerNewUserAction',
                'route_name'        => 'newAccount',
                'front_end'         => '/newAccount'
            ],
            self::SHORTENED_URL_LP => [
                'controller'        => LandingPageController::class .'::LandingPageAction',
                'route_name'        => 'urlShortenedLandingPage',
                'front_end'         => ''
            ],
    //        self::ACCOUNT_VERIFICATION => [
//                'controller'        => 'xxx',
    //            'route_name'        => 'account_verification',
    //            'front_end'         => 'account_verification'
    //        ]
        ];
        $this->addArrayOfNewRoutePairs($routes);
//        dd($this->routeList);
        return $this;
    }

    protected function addArrayOfNewRoutePairs($routes)
    {
        foreach ($routes as $curI => $curRoute) {
            $this->addRoutePairToList($curRoute, $curI);
        }

        return $this;
    }

    /**
     * @param $item
     * add the item to the array, but throw an exception if an array element index already exists (this simply ensures that routes don't overwrite each other.)
     */
    protected function addRoutePairToList($item, $arrIndex): self
    {
        if ($this->doesRouteConstantExist($arrIndex)) {
            throw new \Exception('unable to add the item with array index: "'. $arrIndex .'" to the routeList as an item with this array index already exists.');
        }

        $this->routePairList[$arrIndex] = $item;

        return $this;
    }

    /**
     * Test that route-pair elements exist in the routePairList array
     * and that the controller element (of the routePair) actually exists (this will help detect if it's renamed / not provided when creating new routePairs).
     */
    public function checkRoutePairListIntegrity() {
        foreach($this->routePairList as $curConstant => $curRoutePair) {
//            print 'testing: '. $curRoutePair['route_name'] ."\n";
            // check route_name exists
            $this->getSymfonyRouteNAME($curConstant);

            // check front-end roue exists (note: don't try to generate the urls as we don't have the $data field to add and it will rderNewBadgeTestgenerate an error)
            $this->getFrontendURLPart($curConstant);

            // check that controller exists AND that an actual class/method pair exists in this app
            $this->checkControllerActuallyExists($curConstant);
        }

        return true;
    }
}
?>
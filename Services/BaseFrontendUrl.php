<?php

namespace VisageFour\Bundle\ToolsBundle\Services;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BaseFrontendUrl
{
    /**
     * @var array
     * a list of all possible routes - with a route to the front-end and another to the backend.
     */
    protected $routeList = [];

    // this ensures that a redirection is *specifically* set, so that if a null / false is accidentally returned, that the bug is caught.
    public const NO_REDIRECTION = 'noRedirect';

    private $baseUrl;

    // list of routes constants:
    const LOGIN                     = 100;
    const CONFIRM_EMAIL             = 200;
    const MAIN_LOGGED_IN_USER_MENU  = 300;
    const LOGOUT                    = 400;
    const HOME                      = 500;
    const CHANGE_PASSWORD           = 600;
    const USER_REGISTRATION         = 700;
    const ACCOUNT_VERIFICATION      = 900;

    const NO_FRONTEND = 'NO_FRONTEND';      // placeholder to indicate that there's no "front-end", maybe because the "front-end" is acctually delivered via the backend (not a react client)

    // in this case, use GET on the symfony route_name to get the page.

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

    public function checkIfRouteExists ($constant) { // dont add an type to the parameter
        // check if null was sent
        if (empty($constant)) {
            throw new \Exception(
                'A falsey value (likely Null) was provided as the $constant for in the FrontendUrl class.'.
                " You must explicitly set the $constant value to: FrontendUrl::NO_REDIRECTION to prevent redirection (i.e. don't use null)."
            );
        }

        if (!$this->doesRouteConstantExist($constant)) {
            throw new \Exception (
                "a route with the value: '$constant' has not been configured.'.
                ' Note, this should also be uppercase, if not, you have probably called getFrontendURLPart() twice. (Search for marker: #CMDKKD00 to add new routes)."
            );
        }

//        if (empty($route)) {
//            throw new \Exception ('route_name cannot be empty.');
//        }
    }

    private function doesRouteConstantExist($constant)
    {
//        dd($this->routeList);
        return !empty($this->routeList[$constant]);
    }

    /**
     * @param int $constant
     * @return string
     * @throws \Exception
     *
     * Return the URL of the symfony route (for the $constant provided).
     */
    public function getSymfonyURL (int $constant, array $params = [])
    {
        $route = $this->getSymfonyRouteNAME($constant);

        // generate URL
        $pathPart = $this->router->generate($route['route_name'], $params);
        return $this->baseUrl . $pathPart;
    }

    public function getSymfonyRouteNAME ($constant)
    {
        $this->checkIfRouteExists($constant);

        return $this->routeList[$constant];
    }

    public function getFrontendUrl(int $constant, $data = [])
    {
        $pathPart = $this->getFrontendURLPart($constant);

        $populatedPath = $this->generateURLPart($pathPart, $data);

        return $this->frontend_base_url .'/'. $populatedPath;
    }

    /**
     * @param $constant
     * @param array $data
     *
     * Replace the placeholders with real values from $data.
     */
    private function generateURLPart($pathPart, $data = [])
    {
        // replace the placeholders with their $data values
        $populatedPath = $pathPart;
        foreach($data as $key => $replacementValue) {
            $replacementValue = urlencode($replacementValue);
            $needle = '{'.strtoupper($key).'}';

            if(strpos($populatedPath, $needle) == false) {
                throw new \Exception('Cannot use placeholder named: "'. $needle .'" in URL construction, as it doesn\'t exist in the pathPart: "/'. $pathPart .'"');
            }

            $populatedPath = str_replace($needle, $replacementValue, $populatedPath);
        }

        // check for any remaining placeholders that were not provided a value:
        $leftBracket  = (strpos($populatedPath, '{'));
        if (!empty($leftBracket)) {
            $rightbracket  = (strpos($populatedPath, '}'));
            if (!empty($rightbracket)) {
                $missingPlaceholderName = substr($populatedPath, $leftBracket+1, ($rightbracket - $leftBracket -1));
                throw new \Exception('You must provide a value for the placeholder: "'. $missingPlaceholderName .'"');
            }
        }

        return $populatedPath;
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

        $route = $this->routeList[$constant];

        if ($addBaseUrl == true) {
            return $this->baseUrl .'/'. $route['front_end'];
        }

        return $route['front_end'];
    }

    /**
     * @return $this
     * @throws \Exception
     *
     * add a list of routes to the main routeList
     */
    private function populateRouteList()
    {
        // Add new route marker: #CMDKKD00
        $routes = [
            self::LOGIN => [
                'route_name'        => 'app_login_post',
                'front_end'         => 'login'
            ],
            self::LOGOUT => [
                'route_name'        => 'manual_logout',
                'front_end'         => null
            ],
            self::CONFIRM_EMAIL => [
                'controller'        => 'SecurityController::verifyEmailAccountViaTokenAction',          // controller listed here for debugging (finding the controller quickly).
                'route_name'        => 'confirm_email_get',
                'front_end'         => 'confirm_email/{EMAIL}/{VERIFICATION_TOKEN}'
            ],
            self::MAIN_LOGGED_IN_USER_MENU => [
                'route_name'        => 'main_loggedin_user_menu',
                'front_end'         => 'userMenu'
            ],
            self::HOME => [
                'route_name'        => 'homepage',
                'front_end'         => ''
            ],
            self::CHANGE_PASSWORD => [
                'route_name'        => 'change_password',
                'front_end'         => 'change_password'
            ],
            self::USER_REGISTRATION => [
                'route_name'        => 'reg',
                'front_end'         => self::NO_FRONTEND
            ],
    //        self::ACCOUNT_VERIFICATION => [
    //            'route_name'        => 'account_verification',
    //            'front_end'         => 'account_verification'
    //        ]
        ];

        foreach ($routes as $curI => $curRoute) {
            $this->addToRouteList($curRoute, $curI);
        }
//        dd($this->routeList);
        return $this;
    }

    /**
     * @param $item
     * add the item to the array, but throw an exception if an array element index already exists (this simply ensures that routes don't overwrite each other.)
     */
    protected function addToRouteList($item, $arrIndex): self
    {
        if ($this->doesRouteConstantExist($arrIndex)) {
            throw new \Exception('unable to add the item with array index: "'. $arrIndex .'" to the routeList as an item with this array index already exists.');
        }

        $this->routeList[$arrIndex] = $item;


        return $this;
    }
}
?>
<?php

namespace VisageFour\Bundle\ToolsBundle\Services;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BaseFrontendUrl
{
    // this ensures that a redirection is *specifically* set, so that if a null / false is accidentally returned, that the bug is caught.
    public const NO_REDIRECTION = 'noRedirect';

    private $baseUrl;

    const LOGIN                     = 100;
    const CONFIRM_EMAIL             = 200;
    const MAIN_LOGGED_IN_USER_MENU  = 300;
    const LOGOUT                    = 400;
    const HOME                      = 500;
    const CHANGE_PASSWORD           = 600;
    const USER_REGISTRATION         = 700;
    const ACCOUNT_VERIFICATION      = 900;

    // Marker: #CMDKKD00
    const NO_FRONTEND = 'NO_FRONTEND';      // placeholder to indicate that there's no "front-end", maybe because the "front-end" is acctually delivered via the backend (not a react client)
    // in this case, use GET on the symfony route_name to get the page.
    static public $routes = [
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
    }

    static public function checkIfRouteExists ($constant) { // dont add an type to the parameter
        // check if Null was sent
        if (empty($constant)) {
            throw new \Exception(
                'A falsey value (likely Null) was provided as the $constant for in the FrontendUrl class.'.
                " You must explicitly set the $constant value to: FrontendUrl::NO_REDIRECTION to prevent redirection (i.e. don't use null)."
            );
        }

        if (empty(self::$routes[$constant])) {
            $extra = (is_string($constant)) ? ' Also, the provided value was a string, it should be a number. This is likely because you have called getFrontendURLPart() twice.' : '';
            throw new \Exception (
                "a route with the value: '$constant' has not beeen coigured. (Search for marker: #CMDKKD00 to add new routes)."
                .$extra
            );
        }

//        if (empty($route)) {
//            throw new \Exception ('route_name cannot be empty.');
//        }
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
        self::checkIfRouteExists($constant);

        return self::$routes[$constant];
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
        self::checkIfRouteExists($constant);

        $route = self::$routes[$constant];

        if ($addBaseUrl == true) {
            return $this->baseUrl .'/'. $route['front_end'];
        }

        return $route['front_end'];
    }
}
?>
<?php
    // Open the existing or create a new session
    session_start();

    // Application global constants
    define('APP_ENV',     'dev');
    define('APP_DIR',     dirname(__FILE__));
    define('APP_VERSION', '0.0.1');

    // Import the class loader
    require_once('autoloader.php');

    // Application core
    use App\Utils;
    use App\Logger;
    use App\Renderer;
    use App\Security;
    // Data handling
    use Data\Database;
    // Functions
    use Functions\MapInfo;
    use Functions\Upload;
    use Functions\Download;
    use Functions\Rate;
    use Functions\User;
    // Views
    use Functions\Views\Home;
    use Functions\Views\About;
    use Functions\Views\Dashboard;
    use Functions\Views\Profile;
    use Functions\Views\Settings;
    use Functions\Views\MapDetails;
    use Functions\Views\NewMap;
    use Functions\Views\Result;

    // Global variables
    $config  = require_once(__DIR__ . '/include/config/' . APP_ENV . '_config.php');
    $request = null;
    $logger  = new App\Logger(APP_DIR . '/logs/' . date('Y-m-d') . '.log');

    class App
    {
        /**
         **  The utilities class
         **/
        private $utils       = null;
        /**
         **  The HTML renderer class
         **/
        private $renderer    = null;
        /**
         **  The security class
         **/
        private $security    = null;
        /**
         **  The database handler class
         **/
        private $dbHandler   = null;

        public function __construct() {
            global $request, $logger;

            // Initialize the globally required classes
            $this -> utils     = new App\Utils();
            $this -> dbHandler = new Data\Database();
            $this -> renderer  = new App\Renderer();
            $this -> security  = new App\Security($this -> utils);

            // Parse the request to usable variables
            $request = $this -> utils -> parse_path();
            $logger -> log('request : ' . print_r($request, True), Logger::DEBUG);
        }

        public function start() {
            global $request;

            $this -> security -> checkRememberMe($this -> dbHandler);

            if ($request['call'] === 'about') { // Show the 'About' page
                $aboutFunc  = new Functions\Views\About();
                $pageHeader = '<ol class="breadcrumb">' . PHP_EOL .
                              '    <li class="active">About</li>' . PHP_EOL .
                              '</ol>' . PHP_EOL .
                              '<div class="row spacer"></div>' . PHP_EOL;

                // Set the page title
                $this -> renderer -> setValue('title', 'About');
                $this -> renderer -> setValue('header', $pageHeader);
                // Set the active tab
                $this -> renderer -> setValue('home-active', '');
                $this -> renderer -> setValue('about-active', 'class="active"');

                $content = $aboutFunc -> getContent();

                // Set the content
                $this -> renderer -> setContent($content);
//////////////////////////////////////////////////////////////////////////////
/// User calls
///
            } elseif ($request['call'] === 'register') { // Handle the registration request
                if (!Empty(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW || 
                                                                                        FILTER_FLAG_STRIP_HIGH ||
                                                                                        FILTER_FLAG_STRIP_BACKTICK)) ||
                    !Empty(filter_input(INPUT_POST, 'emailAddress', FILTER_SANITIZE_EMAIL)) ||
                    Empty(filter_input(INPUT_POST, 'confirmEmailAddress', FILTER_UNSAFE_RAW)) || // Simple 'dumb' bot prevention
                    !Empty(filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW)) ||
                    !Empty(filter_input(INPUT_POST, 'confirmPassword', FILTER_UNSAFE_RAW)) ||
                    !Empty(filter_input(INPUT_POST, 'g-recaptcha-response', FILTER_UNSAFE_RAW))) {
                    $this -> utils -> http_response_code(400);
                    Die($this -> utils -> http_code_to_text(400));
                };

                $resultFunc = new Functions\Views\Result();
                $pageHeader = '<ol class="breadcrumb">' . PHP_EOL .
                              '    <li><a href="/dashboard">Home</a></li>' . PHP_EOL .
                              '    <li class="active">Register</li>' . PHP_EOL .
                              '</ol>' . PHP_EOL .
                              '<div class="row spacer"></div>' . PHP_EOL;

                // Set the page title
                $this -> renderer -> setValue('title', 'Register');
                $this -> renderer -> setValue('header', $pageHeader);
                // Set the active tab
                $this -> renderer -> setValue('home-active', 'class="active"');
                $this -> renderer -> setValue('about-active', '');

                $result  = $this -> security -> register($this -> dbHandler);
                $content = $resultFunc -> getContent($result['status'], $result['message'], $this -> dbHandler);

                header('Refresh:5; url=/home');
                // Set the content
                $this -> renderer -> setContent($content);
            } elseif ($request['call'] === 'login') { // Handle the login request
                $resultFunc = new Functions\Views\Result();
                $pageHeader = '<ol class="breadcrumb">' . PHP_EOL .
                              '    <li><a href="/dashboard">Home</a></li>' . PHP_EOL .
                              '    <li class="active">Login</li>' . PHP_EOL .
                              '</ol>' . PHP_EOL .
                              '<div class="row spacer"></div>' . PHP_EOL;

                // Set the page title
                $this -> renderer -> setValue('title', 'Login');
                $this -> renderer -> setValue('header', $pageHeader);
                // Set the active tab
                $this -> renderer -> setValue('home-active', 'class="active"');
                $this -> renderer -> setValue('about-active', '');

                $result  = $this -> security -> login($this -> dbHandler);
                $content = $resultFunc -> getContent($result['status'], $result['message'], $this -> dbHandler);

                header('Refresh:5; url=/home');
                // Set the content
                $this -> renderer -> setContent($content);
            } elseif ($request['call'] === 'logout') { // Handle the logout request
                $resultFunc = new Functions\Views\Result();
                $pageHeader = '<ol class="breadcrumb">' . PHP_EOL .
                              '    <li><a href="/dashboard">Home</a></li>' . PHP_EOL .
                              '    <li class="active">Logout</li>' . PHP_EOL .
                              '</ol>' . PHP_EOL .
                              '<div class="row spacer"></div>' . PHP_EOL;

                // Set the page title
                $this -> renderer -> setValue('title', 'Logout');
                $this -> renderer -> setValue('header', $pageHeader);
                // Set the active tab
                $this -> renderer -> setValue('home-active', 'class="active"');
                $this -> renderer -> setValue('about-active', '');

                $result  = $this -> security -> logout($this -> dbHandler);
                $content = $resultFunc -> getContent($result['status'], $result['message'], $this -> dbHandler);

                header('Refresh:5; url=/home');
                // Set the content
                $this -> renderer -> setContent($content);
            } elseif ($request['call'] === 'dashboard' &&
                      property_exists($_SESSION['user'], 'id') &&
                      property_exists($_SESSION['user'], 'group') &&
                      $_SESSION['user'] -> id != 0 &&
                      $_SESSION['user'] -> group >= 5) { // Show the dashboard
                $dashboardFunc = new Functions\Views\Dashboard($this -> utils);
                $pageHeader    = '<ol class="breadcrumb">' . PHP_EOL .
                                 '    <li class="active">Dashboard</li>' . PHP_EOL .
                                 '</ol>' . PHP_EOL .
                                 '<div class="row spacer"></div>' . PHP_EOL;

                // Set the page title
                $this -> renderer -> setValue('title', 'Dashboard');
                $this -> renderer -> setValue('header', $pageHeader);
                // Set the active tab
                $this -> renderer -> setValue('home-active', '');
                $this -> renderer -> setValue('about-active', '');

                $content = $dashboardFunc -> getContent($this -> dbHandler);

                // Set the content
                $this -> renderer -> setContent($content);
            } elseif ($request['call_parts'][0] === 'profile' &&
                      isset($request['call_parts'][1])) { // Show the user's profile
                $profileFunc = new Functions\Views\Profile($this -> utils);
                $pageHeader  = '<ol class="breadcrumb">' . PHP_EOL .
                               '    <li class="active">Profile</li>' . PHP_EOL .
                               '</ol>' . PHP_EOL .
                               '<div class="row spacer"></div>' . PHP_EOL;

                // Set the page title
                $this -> renderer -> setValue('title', 'Profile');
                $this -> renderer -> setValue('header', $pageHeader);
                // Set the active tab
                $this -> renderer -> setValue('home-active', '');
                $this -> renderer -> setValue('about-active', '');

                $content = $profileFunc -> getContent($this -> dbHandler);

                // Set the content
                $this -> renderer -> setContent($content);
            } elseif ($request['call'] === 'settings' &&
                      property_exists($_SESSION['user'], 'id') &&
                      $_SESSION['user'] -> id != 0) { // Show the 'user settings' page
                $settingsFunc = new Functions\Views\Settings($this -> utils);
                $pageHeader   = '<ol class="breadcrumb">' . PHP_EOL .
                                '    <li><a href="/home">Home</a></li>' . PHP_EOL .
                                '    <li class="active">Settings</li>' . PHP_EOL .
                                '</ol>' . PHP_EOL .
                                '<div class="row spacer"></div>' . PHP_EOL;

                // Set the page title
                $this -> renderer -> setValue('title', 'Settings');
                $this -> renderer -> setValue('header', $pageHeader);
                // Set the active tab
                $this -> renderer -> setValue('home-active', '');
                $this -> renderer -> setValue('about-active', '');

                $content = $settingsFunc -> getContent($this -> dbHandler);

                // Set the content
                $this -> renderer -> setContent($content);
            } elseif ($request['call'] === 'updatesettings' &&
                      property_exists($_SESSION['user'], 'id') &&
                      $_SESSION['user'] -> id != 0) { // Update the user's info
                $userFunc   = new Functions\User($this -> utils);
                $resultFunc = new Functions\Views\Result();
                $pageHeader = '<ol class="breadcrumb">' . PHP_EOL .
                              '    <li><a href="/home">Home</a></li>' . PHP_EOL .
                              '    <li class="active">Update Settings</li>' . PHP_EOL .
                              '</ol>' . PHP_EOL .
                              '<div class="row spacer"></div>' . PHP_EOL;

                // Set the page title
                $this -> renderer -> setValue('title', 'Update Settings');
                $this -> renderer -> setValue('header', $pageHeader);
                // Set the active tab
                $this -> renderer -> setValue('home-active', '');
                $this -> renderer -> setValue('about-active', '');

                $result  = $userFunc -> updateUserInfo($this -> security, $this -> dbHandler);
                $content = $resultFunc -> getContent($result['status'], $result['message'], $this -> dbHandler);

                header('Refresh:5; url=/settings');
                // Set the content
                $this -> renderer -> setContent($content);
//////////////////////////////////////////////////////////////////////////////
/// Map calls
///
            } elseif ($request['call_parts'][0] === 'mapdetails') { // Show the 'Map Details' page
                if (Empty($request['call_parts'][1])) {
                    header('HTTP/1.1 404 Not Found');
                    header('Location: /home');
                    Exit;
                };

                $mapDetailFunc = new Functions\Views\MapDetails($this -> utils);
                $pageHeader    = '<ol class="breadcrumb">' . PHP_EOL .
                                 '    <li><a href="/home">All Maps</a></li>' . PHP_EOL .
                                 '    <li class="active">Map Details</li>' . PHP_EOL .
                                 '</ol>' . PHP_EOL .
                                 '<div class="row spacer"></div>' . PHP_EOL;

                // Set the page title
                $this -> renderer -> setValue('title', 'Map Details');
                $this -> renderer -> setValue('header', $pageHeader);
                // Set the active tab
                $this -> renderer -> setValue('home-active', 'class="active"');
                $this -> renderer -> setValue('about-active', '');

                $content = $mapDetailFunc -> getContent($this -> dbHandler);

                // Set the content
                $this -> renderer -> setContent($content);
            } elseif ($request['call'] === 'newmap' &&
                      property_exists($_SESSION['user'], 'id') &&
                      $_SESSION['user'] -> id != 0 &&
                      $_SESSION['user'] -> group != 0) { // Show the 'New Map' page
                $newMapFunc = new Functions\Views\NewMap();
                $pageHeader = '<ol class="breadcrumb">' . PHP_EOL .
                              '    <li><a href="/dashboard">Dashboard</a></li>' . PHP_EOL .
                              '    <li class="active">New Map</li>' . PHP_EOL .
                              '</ol>' . PHP_EOL .
                              '<div class="row spacer"></div>' . PHP_EOL;

                // Set the page title
                $this -> renderer -> setValue('title', 'New Map');
                $this -> renderer -> setValue('header', $pageHeader);
                // Set the active tab
                $this -> renderer -> setValue('home-active', '');
                $this -> renderer -> setValue('about-active', '');

                $content = $newMapFunc -> getContent($this -> dbHandler);

                // Set the content
                $this -> renderer -> setContent($content);
            } elseif ($request['call'] === 'upload' &&
                      property_exists($_SESSION['user'], 'id') &&
                      $_SESSION['user'] -> id != 0 &&
                      $_SESSION['user'] -> group >= 5 &&
                      filter_input(INPUT_SERVER, 'REQUEST_METHOD') == 'POST') { // Handle the upload request
                $uploadFunc = new Functions\Upload($this -> utils);
                $resultFunc = new Functions\Views\Result();
                $pageHeader = '<ol class="breadcrumb">' . PHP_EOL .
                              '    <li><a href="/dashboard">Dashboard</a></li>' . PHP_EOL .
                              '    <li><a href="/newmap">New Map</a></li>' . PHP_EOL .
                              '    <li class="active">Upload</li>' . PHP_EOL .
                              '</ol>' . PHP_EOL .
                              '<div class="row spacer"></div>' . PHP_EOL;

                // Set the page title
                $this -> renderer -> setValue('title', 'Upload');
                $this -> renderer -> setValue('header', $pageHeader);
                // Set the active tab
                $this -> renderer -> setValue('home-active', '');
                $this -> renderer -> setValue('about-active', '');

                $result  = $uploadFunc -> getContent($this -> dbHandler);
                $content = $resultFunc -> getContent($result['status'], $result['message'], $this -> dbHandler);

                header('Refresh:5; url=/dashboard');
                // Set the content
                $this -> renderer -> setContent($content);
//////////////////////////////////////////////////////////////////////////////
/// Default to home
///
            } else { // Show the 'Home' page
                $homeFunc   = new Functions\Views\Home();
                $pageHeader = '<ol class="breadcrumb">' . PHP_EOL .
                              '    <li class="active">All Maps</li>' . PHP_EOL .
                              '</ol>' . PHP_EOL .
                              '<div class="row spacer"></div>' . PHP_EOL;

                // Set the page title
                $this -> renderer -> setValue('title', 'Home');
                $this -> renderer -> setValue('header', $pageHeader);
                // Set the active tab
                $this -> renderer -> setValue('home-active', 'class="active"');
                $this -> renderer -> setValue('about-active', '');

                $content = $homeFunc -> getContent($this -> dbHandler);

                // Set the content
                $this -> renderer -> setContent($content);
            };

            // Cleanup
            $this -> dbHandler -> Destroy();
            // Render and show the output
            print($this -> renderer -> output());
        }
    }

    // Create and start the applocation
    $app = new App();
    $app -> start();

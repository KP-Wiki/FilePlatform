<?php
    // Open the existing or create a new session
    session_start();

    // Application global constants
    define('APP_ENV',     'dev');
    define('APP_DIR',     dirname(__FILE__));
    define('APP_VERSION', '0.0.1');

    require_once('autoloader.php');

    // Application core
    use App\Utils;
    use App\Logger;
    use App\Renderer;
    use App\Security;
    // Data handling
    use Data\Database;
    use Data\Files;
    // Functions
    use Functions\MapInfo;
    use Functions\Upload;
    use Functions\Download;
    use Functions\Rate;
    // Views
    use Functions\Views\Home;
    use Functions\Views\About;
    use Functions\Views\Dashboard;
    use Functions\Views\MapDetails;
    use Functions\Views\NewMap;

    // Global variables
    $config  = require_once(__DIR__ . '/include/config/' . APP_ENV . '_config.php');
    $request = null;
    $logger  = new App\Logger(APP_DIR . '/logs/' . date('Y-m-d') . '.log');

    class App
    {
        private $utils       = null;
        private $renderer    = null;
        private $security    = null;
        private $dbHandler   = null;
        private $fileHandler = null;

        public function __construct() {
            global $config, $request, $logger;

            // Initialize the globally required classes
            $this -> utils       = new App\Utils();
            $this -> renderer    = new App\Renderer();
            $this -> security    = new App\Security($this -> utils);
            $this -> dbHandler   = new Data\Database();
            $this -> fileHandler = new Data\Files();

            // Parse the request to usable variables
            $request = $this -> utils -> parse_path();
            $logger -> log('request : ' . print_r($request, True), Logger::DEBUG);
        }

        public function start() {
            global $config, $request;

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
            } elseif ($request['call'] === 'register') { // Handle the registration request
                if (!isset($_POST['username']) ||
                    !isset($_POST['emailAddress']) ||
                    !Empty($_POST['confirmEmailAddress']) || // Simple 'dumb' bot prevention
                    !isset($_POST['password']) ||
                    !isset($_POST['confirmPassword']) ||
                    !isset($_POST['g-recaptcha-response'])) {
                    $this -> utils -> http_response_code(400);
                    Die($this -> utils -> http_code_to_text(400));
                };
                $this -> security -> register($this -> dbHandler);
            } elseif ($request['call'] === 'login') { // Handle the login request
                $this -> security -> login($this -> dbHandler);
            } elseif ($request['call'] === 'logout') { // Handle the logout request
                $this -> security -> logout($this -> dbHandler);
            } elseif ($request['call'] === 'dashboard' &&
                      property_exists($_SESSION['user'], 'id') &&
                      $_SESSION['user'] -> id != 0) { // Show the dashboard
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
                      $_SERVER['REQUEST_METHOD'] == 'POST') { // Handle the upload request
                $uploadFunc   = new Functions\Upload($this -> utils);
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

                $content = $uploadFunc -> getContent($this -> dbHandler);

                header('Refresh:5; url=/dashboard');
                // Set the content
                $this -> renderer -> setContent($content);
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
?>

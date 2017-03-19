<?php
    session_start();

    define('APP_ENV',     'dev');
    define('APP_DIR',     dirname(__FILE__));
    define('APP_VERSION', '0.0.1');

    require_once('autoloader.php');

    use App\Utils;
    use App\Logger;
    use App\Renderer;
    use App\Security;
    use Data\Database;
    use Data\Files;
    use Functions\Home;
    use Functions\About;
    use Functions\MapDetails;
    use Functions\Download;
    use Functions\Rate;

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

            $this -> utils       = new App\Utils();
            $this -> renderer    = new App\Renderer();
            $this -> security    = new App\Security($this -> utils);
            $this -> dbHandler   = new Data\Database();
            $this -> fileHandler = new Data\Files();

            $request = $this -> utils -> parse_path();
            $logger -> log('request : ' . print_r($request, True), Logger::DEBUG);
        }

        public function start() {
            global $config, $request;

            $this -> security -> checkRememberMe($this -> dbHandler);

            if ($request['call'] === 'about') {
                $aboutFunc = new Functions\About();

                // Set the page title
                $this -> renderer -> setValue('title', 'About');
                // Set the active tab
                $this -> renderer -> setValue('home-active', '');
                $this -> renderer -> setValue('about-active', 'class="active"');

                $content = $aboutFunc -> getContent();

                // Set the content
                $this -> renderer -> setContent($content);
            } elseif ($request['call'] === 'mapdetails') {
                if (Empty($request['query_vars']) || Empty($request['query_vars']['map'])) {
                    header('HTTP/1.1 404 Not Found');
                    header('Location: /home');
                    Exit;
                };

                $mapDetailFunc = new Functions\MapDetails($this -> utils);

                // Set the page title
                $this -> renderer -> setValue('title', 'Map Details');
                // Set the active tab
                $this -> renderer -> setValue('home-active', 'class="active"');
                $this -> renderer -> setValue('about-active', '');

                $content = $mapDetailFunc -> getContent($this -> dbHandler);

                // Set the content
                $this -> renderer -> setContent($content);
            } elseif ($request['call'] === 'register') {
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
            } elseif ($request['call'] === 'login') {
                $this -> security -> login($this -> dbHandler);
            } elseif ($request['call'] === 'logout') {
                $this -> security -> logout($this -> dbHandler);
            } else {
                $homeFunc = new Functions\Home();

                // Set the page title
                $this -> renderer -> setValue('title', 'Home');
                // Set the active tab
                $this -> renderer -> setValue('home-active', 'class="active"');
                $this -> renderer -> setValue('about-active', '');

                $content = $homeFunc -> getContent($this -> dbHandler);

                // Set the content
                $this -> renderer -> setContent($content);
            };

            $this -> dbHandler -> Destroy();
            print($this -> renderer -> output());
        }
    }

    $app = new App();
    $app -> start();
?>

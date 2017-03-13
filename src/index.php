<?php
    session_start();

    define('APP_ENV',     'dev');
    define('APP_DIR',     dirname(__FILE__));
    define('APP_VERSION', '0.0.1');

    require_once('autoloader.php');

    use App\Utils;
    use App\Renderer;
    use Data\Database;
    use Data\Files;
    use Functions\Home;
    use Functions\About;
    use Functions\Download;
    use Functions\FileDetails;

    $config  = require_once(__DIR__ . '/include/config/' . APP_ENV . '_config.php');
    $request = null;

    class App
    {
        private $renderer    = null;
        private $utils       = null;
        private $dbHandler   = null;
        private $fileHandler = null;

        public function __construct() {
            global $config, $request;

            $this -> utils       = new App\Utils();
            $this -> renderer    = new App\Renderer();
            $this -> dbHandler   = new Data\Database();
            $this -> fileHandler = new Data\Files();

            $request = $this -> utils -> parse_path();
        }

        public function start() {
            global $config, $request;

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
            } elseif ($request['call'] === 'download') {
                if (Empty($request['query_vars']) || Empty($request['query_vars']['file'])) {
                    header('HTTP/1.1 404 Not Found');
                    header('Location: /home');
                    Exit;
                };

                $downloadFunc = new Functions\Download();
                $fullPath     = $downloadFunc -> getContent($this -> dbHandler);

                if ($fullPath === null)
                    Exit;

                ignore_user_abort(true);
                // Disable the time limit for this script
                set_time_limit(0);

                if ($fileData = fopen ($fullPath, 'r')) {
                    $fileSize  = filesize($fullPath);
                    $pathParts = pathinfo($fullPath);

                    header('Content-type: application/octet-stream');
                    // Use 'attachment' to force a file download
                    header('Content-Disposition: attachment; filename="' . $pathParts['basename'] . '"');
                    header('Content-length: ' . $fileSize);
                    // Use this to open files directly
                    header('Cache-control: private');

                    while(!feof($fileData)) {
                        $buffer = fread($fileData, 2048);
                        echo $buffer;
                    };
                };

                fclose ($fileData);
                Exit;
            } elseif ($request['call'] === 'filedetails') {
                if (Empty($request['query_vars']) || Empty($request['query_vars']['file'])) {
                    header('HTTP/1.1 404 Not Found');
                    header('Location: /home');
                    Exit;
                };

                $fileDetailFunc = new Functions\FileDetails();

                // Set the page title
                $this -> renderer -> setValue('title', 'File Details');
                // Set the active tab
                $this -> renderer -> setValue('home-active', 'class="active"');
                $this -> renderer -> setValue('about-active', '');

                $content = $fileDetailFunc -> getContent($this -> dbHandler);

                // Set the content
                $this -> renderer -> setContent($content);
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

<?php
    define('APP_ENV',     'dev');
    define('APP_DIR',     dirname(__FILE__));
    define('APP_VERSION', '0.0.1');

    require_once('autoloader.php');

    use App\Utils;
    use Data\Database;
    use Data\Files;
    use Functions\Home;
    use Functions\FileDetails;
    use Functions\Download;
    use Functions\Rate;

    $config  = require_once(__DIR__ . '/include/config/' . APP_ENV . '_config.php');
    $request = null;

    class Apiv1
    {
        private $utils       = null;
        private $dbHandler   = null;
        private $fileHandler = null;

        public function __construct() {
            global $config, $request;

            $this -> utils       = new App\Utils();
            $this -> dbHandler   = new Data\Database();
            $this -> fileHandler = new Data\Files();

            $request = $this -> utils -> parse_path();
        }

        public function start() {
            global $config, $request;

            $response = Array();

            if ($request['call_parts'][2] === 'getFiles') {
                $homeFunc = new Functions\Home();

                $response = $homeFunc -> getApiResponse($this -> dbHandler);
            } else {
                $response['Status']  = 'ERROR';
                $response['Message'] = 'Requested function does not exist!';
            };

            $this -> dbHandler -> Destroy();
            print(json_encode($response, JSON_PRETTY_PRINT));
        }
    }

    $api = new Apiv1();
    $api -> start();

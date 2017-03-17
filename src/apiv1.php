<?php
    define('APP_ENV',     'dev');
    define('APP_DIR',     dirname(__FILE__));
    define('APP_VERSION', '0.0.1');

    require_once('autoloader.php');

    use App\Utils;
    use Data\Database;
    use Data\Files;
    use Functions\MapDetails;
    use Functions\Download;
    use Functions\Rate;

    $config  = require_once(__DIR__ . '/include/config/' . APP_ENV . '_config.php');
    $request = null;

    class Apiv1
    {
        /**
         **  The HTTP method used for the request ( GET, POST, PUT or DELETE )
         **/
        private $method = '';
        /**
         **  The utilities class
         **/
        private $utils       = null;
        /**
         **  The database handler class
         **/
        private $dbHandler   = null;
        /**
         **  The file handler class ( Superfluous? )
         **/
        private $fileHandler = null;

        public function __construct() {
            global $config, $request;

            $this -> utils       = new App\Utils();
            $this -> dbHandler   = new Data\Database();
            $this -> fileHandler = new Data\Files();
            $this -> method      = $_SERVER['REQUEST_METHOD'];

            $request = $this -> utils -> parse_path();
        }

        public function start() {
            global $config, $request;

            // Never ever cache an API. It should always be new data.
            header('Cache-Control: no-cache, must-revalidate');
            $response = Array();

            if ($request['call_parts'][2] === 'maps') {
                header('Content-type: application/json');
                $mapDetailFunc = new Functions\MapDetails($this -> utils);

                switch ($this -> method) {
                    case 'POST': {
                        $this -> utils -> http_response_code(501);
                        $response['status']  = 'Error';
                        $response['message'] = $this -> utils -> http_code_to_text(501);
                        break;
                    }
                    case 'GET': {
                        $response = $mapDetailFunc -> getMapDetails($this -> dbHandler);
                        break;
                    }
                    case 'PUT': {
                        $this -> utils -> http_response_code(501);
                        $response['status']  = 'Error';
                        $response['message'] = $this -> utils -> http_code_to_text(501);
                        break;
                    }
                    case 'DELETE': {
                        $this -> utils -> http_response_code(501);
                        $response['status']  = 'Error';
                        $response['message'] = $this -> utils -> http_code_to_text(501);
                        break;
                    }
                    default: {
                        $this -> utils -> http_response_code(405);
                        $response['status']  = 'Error';
                        $response['message'] = $this -> utils -> http_code_to_text(405);
                        break;
                    }
                };

                print(json_encode($response, JSON_PRETTY_PRINT));
            } elseif ($request['call_parts'][2] === 'download') {
                $downloadFunc = new Functions\Download($this -> utils);

                switch ($this -> method) {
                    case 'GET': {
                        $fullPath = $downloadFunc -> getDownload($this -> dbHandler);
                        break;
                    }
                    default: {
                        header('Content-type: application/json');
                        $this -> utils -> http_response_code(405);
                        $response['status']  = 'Error';
                        $response['message'] = $this -> utils -> http_code_to_text(405);

                        print(json_encode($response, JSON_PRETTY_PRINT));
                        Exit;
                    }
                };

                if ($fullPath === null) {
                    header('Content-type: application/json');
                    $response['status']  = 'Error';
                    $response['message'] = $this -> utils -> http_code_to_text($GLOBALS['http_response_code']);

                    print(json_encode($response, JSON_PRETTY_PRINT));
                    Exit;
                };

                ignore_user_abort(True);
                set_time_limit(0); // Disable the time limit for this script

                if ($fileData = fopen ($fullPath, 'r')) {
                    $fileSize  = filesize($fullPath);
                    $pathParts = pathinfo($fullPath);

                    $this -> utils -> http_response_code(200);
                    header('Content-type: application/octet-stream');
                    // Use 'attachment' to force a file download
                    header('Content-Disposition: attachment; filename="' . $pathParts['basename'] . '"');
                    header('Content-length: ' . $fileSize);

                    while(!feof($fileData)) {
                        $buffer = fread($fileData, 2048);
                        echo $buffer;
                    };
                };

                fclose ($fileData);
            } elseif ($request['call_parts'][2] === 'rating') {
                header('Content-type: application/json');
                $rateFunc = new Functions\Rate($this -> utils);

                if (Empty($request['call_parts'][3]) ||
                    Empty($request['query_vars']) ||
                    Empty($request['query_vars']['score'])) {
                    $this -> utils -> http_response_code(404);
                    $response['status']  = 'Error';
                    $response['message'] = $this -> utils -> http_code_to_text(404);
                } else {
                    switch ($this -> method) {
                        case 'POST': {
                            $response = $rateFunc -> insertRating($this -> dbHandler);
                            break;
                        }
                        case 'GET': {
                            $response = $rateFunc -> getRating($this -> dbHandler);
                            break;
                        }
                        default: {
                            $this -> utils -> http_response_code(405);
                            $response['status']  = 'Error';
                            $response['message'] = $this -> utils -> http_code_to_text(405);
                        }
                    };
                };

                print(json_encode($response, JSON_PRETTY_PRINT));
            } else {
                $this -> utils -> http_response_code(404);
                $response['status']  = 'Error';
                $response['message'] = $this -> utils -> http_code_to_text(404);

                print(json_encode($response, JSON_PRETTY_PRINT));
            };

            $this -> dbHandler -> Destroy();
        }
    }

    $api = new Apiv1();
    $api -> start();
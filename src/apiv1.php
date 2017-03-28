<?php
    // Application global constants
    define('APP_ENV',     'dev');
    define('APP_DIR',     dirname(__FILE__));
    define('APP_VERSION', '0.0.1');

    // Import the class loader
    require_once('autoloader.php');

    // Application core
    use App\Utils;
    // Data handling
    use Data\Database;
    // Functions
    use Functions\MapInfo;
    use Functions\Download;
    use Functions\Rate;

    // Global variables
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

        public function __construct() {
            global $config, $request;

            $this -> utils     = new App\Utils();
            $this -> dbHandler = new Data\Database();
            $this -> method    = $_SERVER['REQUEST_METHOD'];

            $request = $this -> utils -> parse_path();
        }

        public function start() {
            global $config, $request;

            // Never ever cache an API. It should always be new data.
            header('Cache-Control: no-cache, must-revalidate');
            $response = Array();

            if ($request['call_parts'][2] === 'maps') {
                header('Content-type: application/json');
                $mapInfoFunc = new Functions\MapInfo($this -> utils);

                switch ($this -> method) {
                    case 'POST': { // Create new map
                        $this -> utils -> http_response_code(501);
                        $response['status']  = 'Error';
                        $response['message'] = $this -> utils -> http_code_to_text(501);
                        break;
                    }
                    case 'GET': { // Get map info
                        $response = $mapInfoFunc -> getMapDetails($this -> dbHandler);
                        break;
                    }
                    case 'PUT': { // Edit/Update map/info
                        $this -> utils -> http_response_code(501);
                        $response['status']  = 'Error';
                        $response['message'] = $this -> utils -> http_code_to_text(501);
                        break;
                    }
                    case 'DELETE': { // Delete map
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
                    case 'GET': { // Get download
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

                if (Empty($request['call_parts'][3])) {
                    $this -> utils -> http_response_code(404);
                    $response['status']  = 'Error';
                    $response['message'] = $this -> utils -> http_code_to_text(404);
                } else {
                    switch ($this -> method) {
                        case 'POST': { // Create new rating
                            $response = $rateFunc -> insertRating($this -> dbHandler);
                            break;
                        }
                        case 'GET': { // Get rating info
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
            } elseif ($request['call_parts'][2] === 'resizedefault') { // More of a util to resize the default images
                header('Content-type: application/json');
                $images   = Array();
                $images[] = APP_DIR . '/uploads/images/kp_2016-08-30_21-29-44.png';
                $images[] = APP_DIR . '/uploads/images/kp_2016-09-03_18-34-31.png';

                foreach ($images as $image) {
                    $imageObject = new \Imagick($image);
                    $this -> utils -> resizeImage($imageObject, $config['images']['maxWidth'], $config['images']['maxHeight']);
                    $imageObject -> writeImage($image);
                    $imageObject -> destroy(); 
                };

                print(json_encode('Success', JSON_PRETTY_PRINT));
            } else {
                header('Content-type: application/json');
                $this -> utils -> http_response_code(404);
                $response['status']  = 'Error';
                $response['message'] = $this -> utils -> http_code_to_text(404);

                print(json_encode($response, JSON_PRETTY_PRINT));
            };

            // Cleanup
            $this -> dbHandler -> Destroy();
        }
    }

    // Create and start the API
    $api = new Apiv1();
    $api -> start();

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

            // Never ever cache an API. It should always be new data.
            header('Cache-Control: no-cache, must-revalidate');

            $response = Array();

            if ($request['call_parts'][2] === 'getMaps') {
                header('Content-type: application/json');

                $mapDetailFunc = new Functions\MapDetails();
                $response      = $mapDetailFunc -> getApiResponse($this -> dbHandler);

                print(json_encode($response, JSON_PRETTY_PRINT));
            } elseif ($request['call_parts'][2] === 'downloadMap') {
                $downloadFunc = new Functions\Download();
                $fullPath     = $downloadFunc -> getApiResponse($this -> dbHandler);

                if ($fullPath === null) {
                    header('Content-type: application/json');

                    $response['Status']  = 'ERROR';
                    $response['Message'] = 'Requested map does not exist!';

                    print(json_encode($response, JSON_PRETTY_PRINT));
                    Exit;
                };

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

                    while(!feof($fileData)) {
                        $buffer = fread($fileData, 2048);
                        echo $buffer;
                    };
                };

                fclose ($fileData);
            } elseif ($request['call_parts'][2] === 'rateMap') {
                header('Content-type: application/json');

                $rateFunc = new Functions\Rate($this -> utils);

                if (Empty($request['call_parts'][3]) ||
                    Empty($request['query_vars']) ||
                    Empty($request['query_vars']['score'])) {
                    $response = 'Unable to process rating';
                } else {
                    $response = $rateFunc -> getApiResponse($this -> dbHandler);
                };

                print(json_encode($response, JSON_PRETTY_PRINT));
            } else {
                $response['Status']  = 'ERROR';
                $response['Message'] = 'Requested function does not exist!';

                print(json_encode($response, JSON_PRETTY_PRINT));
            };

            $this -> dbHandler -> Destroy();
        }
    }

    $api = new Apiv1();
    $api -> start();

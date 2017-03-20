<?php
    namespace App;

    class Utils
    {
        public function __construct() {
        }

        /**
         ** Get the request in array form for nice urls
         **
         ** @return array
         **/
        public function parse_path() {
            $path = Array();

            if (isset($_SERVER['REQUEST_URI'])) {
                $request_path = explode('?', $_SERVER['REQUEST_URI']);

                $path['base']      = rtrim(dirname($_SERVER['SCRIPT_NAME']), '\/');
                $path['call_utf8'] = @substr(urldecode($request_path[0]), strlen($path['base']) + 1);
                $path['call']      = @utf8_decode($path['call_utf8']);

                if ($path['call'] == basename($_SERVER['PHP_SELF']))
                    $path['call'] = '';

                $path['call_parts'] = explode('/', $path['call']);

                $path['query_utf8'] = @urldecode($request_path[1]);
                $path['query']      = @utf8_decode($path['query_utf8']);
                $variables          = @explode('&', $path['query']);

                foreach ($variables as $var) {
                    $kvPair                    = @explode('=', $var);
                    $path['query_vars'][$kvPair[0]] = @$kvPair[1];
                };
            };

            return $path;
        }

        /**
         ** Remove unwanted characters from the given string
         **
         ** @param string $aString
         ** @return string
         **/
        public function cleanInput($aString) {
            $result = str_replace(chr(0), '', $aString);  // Strip null-bytes
            $result = str_replace(' ', '-', $result);     // Replace spaces with underscores
            $result = preg_replace('/-+/', '-', $result); // Replace multiple hyphens with a single one

            return $result;
        }

        /**
         ** Get the client's IP addres
         **
         ** @param  boolean $checkProxy
         ** @return string
         **/
        public function getClientIp($checkProxy = True) {
            if ($checkProxy && isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ipAddr = $_SERVER['HTTP_CLIENT_IP'];
            } else if ($checkProxy && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ipAddr = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ipAddr = $_SERVER['REMOTE_ADDR'];
            };

            return $ipAddr;
        }

        /**
         ** Return the HTTP status text for the provided code
         **
         ** @param integer $Code
         ** @return string
         **/
        public function http_code_to_text($Code) {
            switch($Code) {
                case 100: $Text = 'Continue'; break;
                case 101: $Text = 'Switching Protocols'; break;
                case 200: $Text = 'OK'; break;
                case 201: $Text = 'Created'; break;
                case 202: $Text = 'Accepted'; break;
                case 203: $Text = 'Non-Authoritative Information'; break;
                case 204: $Text = 'No Content'; break;
                case 205: $Text = 'Reset Content'; break;
                case 206: $Text = 'Partial Content'; break;
                case 300: $Text = 'Multiple Choices'; break;
                case 301: $Text = 'Moved Permanently'; break;
                case 302: $Text = 'Moved Temporarily'; break;
                case 303: $Text = 'See Other'; break;
                case 304: $Text = 'Not Modified'; break;
                case 305: $Text = 'Use Proxy'; break;
                case 400: $Text = 'Bad Request'; break;
                case 401: $Text = 'Unauthorized'; break;
                case 402: $Text = 'Payment Required'; break;
                case 403: $Text = 'Forbidden'; break;
                case 404: $Text = 'Not Found'; break;
                case 405: $Text = 'Method Not Allowed'; break;
                case 406: $Text = 'Not Acceptable'; break;
                case 407: $Text = 'Proxy Authentication Required'; break;
                case 408: $Text = 'Request Time-out'; break;
                case 409: $Text = 'Conflict'; break;
                case 410: $Text = 'Gone'; break;
                case 411: $Text = 'Length Required'; break;
                case 412: $Text = 'Precondition Failed'; break;
                case 413: $Text = 'Request Entity Too Large'; break;
                case 414: $Text = 'Request-URI Too Large'; break;
                case 415: $Text = 'Unsupported Media Type'; break;
                case 500: $Text = 'Internal Server Error'; break;
                case 501: $Text = 'Not Implemented'; break;
                case 502: $Text = 'Bad Gateway'; break;
                case 503: $Text = 'Service Unavailable'; break;
                case 504: $Text = 'Gateway Time-out'; break;
                case 505: $Text = 'HTTP Version not supported'; break;
                default:  $Text = 'Unknown http status code ' . $Code; break;
            };
            
            return $Text;
        }

        /**
         ** Set the HTTP response status
         **
         ** @param integer $Code
         ** @return integer
         **/
        public function http_response_code($Code = null) {
            if ($Code !== null) {
                $Text = $this -> http_code_to_text($Code);

                $Protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
                header($Protocol . ' ' . $Code . ' ' . $Text);
                $GLOBALS['http_response_code'] = $Code;
            } else {
                $Code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
            };

            return $Code;
        }

        public function resizeImage(&$imageObject, $maxWidth, $maxHeight) {
            $format = $imageObject -> getImageFormat();

            if ($format == 'GIF') { // If it's a GIF file we need to resize each frame one by one
                $imageObject = $imageObject -> coalesceImages();

                foreach ($imageObject as $frame) { // Gaussian seems better for animations
                    $frame -> resizeImage($maxWidth , $maxHeight , \Imagick::FILTER_GAUSSIAN, 1, True);
                }

                $imageObject = $imageObject -> deconstructImages();
            } else { // Lanczos seems better for static images
                $imageObject -> resizeImage($maxWidth , $maxHeight , \Imagick::FILTER_LANCZOS, 1, True);
            };
        }
    }

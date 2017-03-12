<?php
    namespace App;

    class Utils
    {
        public function __construct() {
        }

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
    }

<?php
    namespace Functions\Views;

    class Result
    {
        public function __construct() {
        }

        public function getContent($type, $message, &$dbHandler) {
            if ($type == 'Success') {
                $resultDiv = '<div class="alert alert-success" role="alert">' . PHP_EOL .
                             '    ' . $message . PHP_EOL .
                             '</div>' . PHP_EOL;
            } else {
                $resultDiv = '<div class="alert alert-danger" role="alert">' . PHP_EOL .
                             '    Something went wrong, please try again later' . PHP_EOL .
                             PHP_EOL .
                             '    Message : ' . $message;
                             '</div>' . PHP_EOL;
            };

            $content = '<div class="row">' . PHP_EOL .
                       '    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-6 col-xs-offset-0 col-sm-offset-0 col-md-offset-2 col-lg-offset-3 toppad">' . PHP_EOL .
                       '        ' . $resultDiv . PHP_EOL .
                       '    </div>' . PHP_EOL .
                       '</div>' . PHP_EOL;

            return $content;
        }
    }

<?php
    namespace Functions\Views;

    class About
    {
        public function __construct() {
        }

        public function getContent() {
            global $request;

            return '<p>Hello :)</p><br />' . PHP_EOL .
                   '<p>Your request :' . PHP_EOL .
                   '    <pre>' . print_r($request, True) . '</pre>' . PHP_EOL .
                   '</p>';
        }
    }

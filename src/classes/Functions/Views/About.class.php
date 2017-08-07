<?php
    namespace Functions\Views;

    class About
    {
        public function __construct() {
        }

        public function getContent() {
            global $request;

            return '<p>' . PHP_EOL .
                   '    Please note that this website is still under heavy development!<br />' . PHP_EOL .
                   '    <br />' . PHP_EOL .
                   '    The following is still planned:.<br />' . PHP_EOL .
                   '    <pre>' . PHP_EOL .
                   '        1> Submission moderation queues for the end-user\'s security.' . PHP_EOL .
                   '        2> Map image updating for submissions.' . PHP_EOL .
                   '        3> Map revision rollback for submitters.' . PHP_EOL .
                   '        4> Improved API features for integration with Knights Province (And perhaps KaM Remake).' . PHP_EOL .
                   '        5> Functional map and user flagging for moderation features.' . PHP_EOL .
                   '        6> More in the future. :)' . PHP_EOL .
                   '    </pre>' . PHP_EOL .
                   '</p><br />' . PHP_EOL .
                   '<p>' . PHP_EOL .
                   '    Your request :' . PHP_EOL .
                   '    <pre>' . print_r($request, True) . '</pre>' . PHP_EOL .
                   '</p>';
        }
    }

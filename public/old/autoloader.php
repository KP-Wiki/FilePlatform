<?php
    class Autoloader {
        static public function loader($class) {
            $filename = __DIR__ . '/include/classes/' . str_replace('\\', '/', $class) . '.class.php';

            if (file_exists($filename)) {
                require_once($filename);

                if (class_exists($class))
                    return True;
            };

            return False;
        }
    }

    spl_autoload_register('Autoloader::loader');

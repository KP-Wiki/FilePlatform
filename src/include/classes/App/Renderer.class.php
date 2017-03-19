<?php
    namespace App;

    /**
     ** HTML template rendering engine for 'maps.kp-wiki.org'
     **
     ** @link https://kp-wiki.org/ Knights Province fan community
     ** @author Thimo Braker <thibmorozier@gmail.com>
     ** @version 1.0
     **/
    class Renderer
    {
        /**
         ** Main template file
         **/
        private $mainTemplate = '';
        /**
         ** Navigation template file
         **/
        private $navTemplate = '';
        /**
         ** User navigation template file
         **/
        private $userNavTemplate = '';
        /**
         ** Contributor navigation template file
         **/
        private $contributorNavTemplate = '';
        /**
         ** Admin navigation template file
         **/
        private $adminNavTemplate = '';
        /**
         ** Main content template file
         **/
        private $content = null;
        /**
         ** Array of values, the keys should match the tags in the template files
         **/
        private $values = Array();
        /**
         ** Identifier to determine whether to show the admin menu or not
         **/
        private $showAdmin = False;

        public function __construct() {
            global $config;

            $this -> mainTemplate           = $config['tpl']['main'];
            $this -> navTemplate            = $config['tpl']['nav'];
            $this -> userNavTemplate        = $config['tpl']['userNav'];
            $this -> contributorNavTemplate = $config['tpl']['contribNav'];
            $this -> adminNavTemplate       = $config['tpl']['adminNav'];
        }

        /**
         ** Sets a value for replacing a specific tag
         **/
        public function setValue($key, $value) {
            $this -> values[$key] = $value;
        }

        /**
         ** Sets the page content
         **/
        public function setContent($value) {
            $this -> content = $value;
        }
        
        /**
         ** Outputs the content of the template, replacing the keys for its respective values
         **/
        public function output() {
            if (!file_exists($this -> mainTemplate))
                return 'Error loading main template file (' . $this -> mainTemplate . ').<br />';

            if (!file_exists($this -> navTemplate))
                return 'Error loading navbar template file (' . $this -> mainTemplate . ').<br />';

            // Load the main template into the output variable
            $output  = file_get_contents($this -> mainTemplate);
            // Insert the content into the output variable
            $output  = str_replace('[@content]', $this -> content, $output);

            // Determine which menu to show
            switch ($_SESSION['user'] -> group) {
                case 1: {
                    $navMenu = file_get_contents($this -> userNavTemplate);
                    break;
                }
                case 5: {
                    $navMenu = file_get_contents($this -> contributorNavTemplate);
                    break;
                }
                case 10: {
                    $navMenu = file_get_contents($this -> adminNavTemplate);
                    break;
                }
                default:
                    $navMenu = file_get_contents($this -> navTemplate);
            }

            // Insert the nav bar into the output variable
            $output  = str_replace('[@nav]', $navMenu, $output);

            // Replace tags with the defined values
            foreach ($this -> values as $key => $value) {
                $tagToReplace = '[@' . $key . ']';
                $output       = str_replace($tagToReplace, $value, $output);
            };

            return $output;
        }
    }

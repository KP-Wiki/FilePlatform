<?php
    namespace Functions;

    class Upload
    {
        private $utils = null;

        public function __construct(&$utilsClass) {
            $this -> utils = $utilsClass;
        }
    }

<?php
    /**
     * Misc utilities
     *
     * PHP version 7
     *
     * @package    MapPlatform
     * @subpackage Core\Utils
     * @author     Thimo Braker <thibmorozier@gmail.com>
     * @version    1.0.0
     * @since      First available since Release 1.0.0
     */
    namespace MapPlatform\Core\Utils;

    use \Slim\Container;
    use \InvalidArgumentException;

    /**
     * Misc utilities
     *
     * @package    MapPlatform
     * @subpackage Core\Utils
     * @author     Thimo Braker <thibmorozier@gmail.com>
     * @version    1.0.0
     * @since      First available since Release 1.0.0
     */
    class MiscUtils
    {
        /** @var \Slim\Container $container The framework container */
        private $container;

        /**
         * MiscUtils constructor.
         *
         * @param \Slim\Container The application controller.
         */
        public function __construct(Container &$aContainer) {
            $this->container = $aContainer;
        }

        /**
         * Generate a v4 GUID
         *
         * @return string
         */
        public function guidv4() {
            $guidKey = openssl_random_pseudo_bytes(16);

            $guidKey[6] = chr(ord($guidKey[6]) & 0x0f | 0x40); // set version to 0100
            $guidKey[8] = chr(ord($guidKey[8]) & 0x3f | 0x80); // set bits 6-7 to 10

            return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($guidKey), 4));
        }
		
		/**
         * Get the client's IP address
         *
         * @param boolean Determine wether or not to check past the proxy
         * @return string
         */
        public function getClientIp($checkProxy = True) {
            if ($checkProxy && !Empty(filter_input(INPUT_SERVER, 'HTTP_CLIENT_IP', FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ||
                                                                                                       FILTER_FLAG_IPV6))) {
                $ipAddr = filter_input(INPUT_SERVER, 'HTTP_CLIENT_IP', FILTER_DEFAULT);
            } else if ($checkProxy && !Empty(filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED_FOR', FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ||
                                                                                                                    FILTER_FLAG_IPV6))) {
                $ipAddr = filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED_FOR', FILTER_DEFAULT);
            } else {
                $ipAddr = filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_DEFAULT);
            };

            return $ipAddr;
        }
		
		/**
         * Get a Gravatar image tag for the specified email address
         *
         * @param string The email address
         * @param string Size in pixels, defaults to 80px [ 1 - 2048 ]
         * @param string Default image set to use [ 404 | mm | identicon | monsterid | wavatar ]
         * @param string Maximum rating (inclusive) [ g | pg | r | x ]
         * @return string Image tag
         */
        function getGravatar($emailAddress, $size = 80, $defaultImg = 'mm', $rate = 'g') {
            $url    = 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($emailAddress))) .
                      '?s=' . $size . '&d=' . $defaultImg . '&r=' . $rate;
            $result = '<img class="img-rounded img-responsive" alt="" src="' . $url . '" />';

            return $result;
        }
    }

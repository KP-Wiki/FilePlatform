<?php
    /**
     * The Constants class
     *
     * This package should be used for all constants
     *
     * PHP version 7
     *
     * @package MapPlatform
     * @subpackage Core
     * @author  Thimo Braker <thibmorozier@gmail.com>
     * @version 1.0.0
     * @since   First available since Release 1.0.0
     */
    namespace MapPlatform\Core;

    /**
     * Constants
     *
     * @package    MapPlatform
     * @subpackage Core
     * @author     Thimo Braker <thibmorozier@gmail.com>
     * @version    1.0.0
     * @since      First available since Release 1.0.0
     */
    abstract class Constants
    {
        /**
         * String input filter flags
         *
         * @var int STRING_FILTER_FLAGS
         */
        const STRING_FILTER_FLAGS = FILTER_FLAG_STRIP_LOW || FILTER_FLAG_STRIP_HIGH || FILTER_FLAG_STRIP_BACKTICK || FILTER_FLAG_ENCODE_AMP;
        /**
         * Shorter string input filter flags
         *
         * @var int STRING_FILTER_FLAGS_SHORT
         */
        const STRING_FILTER_FLAGS_SHORT = FILTER_FLAG_STRIP_LOW || FILTER_FLAG_STRIP_HIGH || FILTER_FLAG_STRIP_BACKTICK;
        /**
         * Hashing function algorithm
         *
         * @var int HASH_ALGO
         */
        const HASH_ALGO = PASSWORD_BCRYPT;
        /**
         * Hashing function options
         *
         * @var array HASH_OPTIONS
         */
        const HASH_OPTIONS = ['cost' => 12];
    }

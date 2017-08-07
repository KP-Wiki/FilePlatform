<?php
    /**
     * Generic data formatting and manipulation utilities
     *
     * PHP version 7
     *
     * @package    ManagementTools
     * @subpackage Core\Utils
     * @author     Thimo Braker <t.braker@sigmax.nl>
     * @version    1.0.0
     * @since      First available since Release 1.0.0
     */
    namespace ManagementTools\Core\Utils;

    use InvalidArgumentException;

    /**
     * Data formatting/manipulation utilities
     *
     * @package    ManagementTools
     * @subpackage Core\Utils
     * @author     Thimo Braker <t.braker@sigmax.nl>
     * @version    1.0.0
     * @since      First available since Release 1.0.0
     */
    class FormattingUtils
    {
        /**
         * FormattingUtils constructor.
         */
        public function __construct() {
        }

        /**
         * Transform an array into a string of options for a 'select input'.
         *
         * @param array $aValues Array of strings to form into options, if key-value pairs are used, then the key will be the value and the value will be the text.
         * @param bool $setValues
         *
         * @return string
         *
         * @throws InvalidArgumentException
         * @throws RuntimeException
         */
        public function arrayToOptions(array &$aValues, bool $setValues = False) {
            $result = '';

            foreach ($aValues as $k => $v) {
                if ($result == '')
                    $result .="<option" . ($setValues ? " value=\"" . $k . "\"" : "") . ">" . $v . "</option>";
                else
                    $result .= PHP_EOL . "<option" . ($setValues ? " value=\"" . $k . "\"" : "") . ">" . $v . "</option>";
            };

            return $result;
        }
    }

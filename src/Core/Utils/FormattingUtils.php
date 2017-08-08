<?php
    /**
     * Generic data formatting and manipulation utilities
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

    use InvalidArgumentException;

    /**
     * Data formatting/manipulation utilities
     *
     * @package    MapPlatform
     * @subpackage Core\Utils
     * @author     Thimo Braker <thibmorozier@gmail.com>
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

        /**
         * Get the difference between two dates 
         *   RESULT FORMAT:
         *     '%y Year %m Month %d Day %h Hours %i Minute %s Seconds' =>  1 Year 3 Month 14 Day 11 Hours 49 Minute 36 Seconds
         *     '%y Year %m Month %d Day'                               =>  1 Year 3 Month 14 Days
         *     '%m Month %d Day'                                       =>  3 Month 14 Day
         *     '%d Day %h Hours'                                       =>  14 Day 11 Hours
         *     '%d Day'                                                =>  14 Days
         *     '%h Hours %i Minute %s Seconds'                         =>  11 Hours 49 Minute 36 Seconds
         *     '%i Minute %s Seconds'                                  =>  49 Minute 36 Seconds
         *     '%h Hours                                               =>  11 Hours
         *     '%a Days                                                =>  468 Days
         *
         * @param string First date
         * @param string Second date
         * @param string Output format
         *
         * @return string
         */
        function dateDifference($aDate1 , $aDate2 , $outFormat = '%a') {
            $dateTime1 = date_create($aDate1);
            $dateTime2 = date_create($aDate2);
            $diff      = date_diff($dateTime1, $dateTime2);
        
            return $diff->format($outFormat);
        
        }
    }

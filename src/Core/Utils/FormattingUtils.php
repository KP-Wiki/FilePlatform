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

    use \Slim\Container;
    use \InvalidArgumentException;

    /**
     * Data formatting/manipulation utilities
     *
     * @package    MapPlatform
     * @subpackage Core\Utils
     * @author     Thimo Braker <thibmorozier@gmail.com>
     * @version    1.0.0
     * @since      First available since Release 1.0.0
     */
    final class FormattingUtils
    {
        /** @var \Slim\Container $container The framework container */
        private $container;

        /**
         * FormattingUtils constructor.
         *
         * @param \Slim\Container The application controller.
         */
        public function __construct(Container &$aContainer)
        {
            $this->container = $aContainer;
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
        public function arrayToOptions(&$aValues, $setValues = false)
        {
            $result = '';

            foreach ($aValues as $k => $v) {
                $result .= "<option" . ($setValues ? " value=\"" . $k . "\"" : "") . ">" . $v . "</option>";
            }

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
         * @param datetime First date
         * @param datetime Second date
         * @param string Output format
         *
         * @return string
         */
        function dateDifference($aDate1, $aDate2, $outFormat = '%a')
        {
            $diffObj = date_diff($aDate1, $aDate2);
            $diff = $diffObj->format($outFormat);
            $this->container->logger->debug('dateDifference -> aDate1 = ' . $aDate1->format('Y/m/d H:i:s') . PHP_EOL .
                                            'aDate2 = ' . $aDate2->format('Y/m/d H:i:s') . PHP_EOL .
                                            'outFormat = ' . $outFormat . PHP_EOL .
                                            'diff = ' . $diff);
            return $diff;
        }
    }

<?php
    /**
     * Generic HTML rendering utilities
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
    use Slim\Http\Response;
    use Slim\Container;

    /**
     * HTML page rendering utilities
     *
     * @package    MapPlatform
     * @subpackage Core\Utils
     * @author     Thimo Braker <thibmorozier@gmail.com>
     * @version    1.0.0
     * @since      First available since Release 1.0.0
     */
    class RenderUtils
    {
        /**
         * RenderUtils constructor.
         */
        public function __construct() {
        }

        /**
         * Get the text inside of a file.
         *
         * @param string $aPageTitle
         * @param int $aPageID
         * @param string $aContentTemplate
         * @param \Slim\Http\Response $aResponse
         * @param array $aValues
         * @param \Slim\Container $aContainer
         *
         * @return \Slim\Http\Response
         *
         * @throws InvalidArgumentException
         * @throws RuntimeException
         */
        public function render(string $aPageTitle, int $aPageID, string $aContentTemplate,
                               Response &$aResponse, array &$aValues, Container &$aContainer) {
            $aValues['PageTitle']   = $aPageTitle;
            $aValues['PageID']      = $aPageID;
            $aValues['PageNav']     = $aContainer->renderer->fetch('nav.phtml', $aValues);
            $aValues['PageContent'] = $aContainer->renderer->fetch($aContentTemplate, $aValues);
            $aValues['PageFooter']  = $aContainer->renderer->fetch('footer.phtml', $aValues);

            return $aContainer->renderer->render($aResponse, 'frame.phtml', $aValues);
        }
    }

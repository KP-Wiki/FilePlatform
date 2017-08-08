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
         * @param string $aNavFile
         * @param \Slim\Http\Response $aResponse
         * @param array $aValueArray
         * @param \Slim\Container $aContainer
         *
         * @return \Slim\Http\Response
         *
         * @throws InvalidArgumentException
         * @throws RuntimeException
         */
        public function render($aPageTitle, $aPageId, $aContentTemplate,
                               Response &$aResponse, &$aValueArray, Container &$aContainer) {
            $navFile = $this->getNavFile();

            $aValueArray['PageTitle']   = $aPageTitle;
            $aValueArray['PageID']      = $aPageId;
            $aValueArray['PageNav']     = $aContainer->renderer->fetch($navFile, $aValueArray);
            $aValueArray['PageContent'] = $aContainer->renderer->fetch($aContentTemplate, $aValueArray);
            $aValueArray['PageFooter']  = $aContainer->renderer->fetch('footer.phtml', $aValueArray);

            return $aContainer->renderer->render($aResponse, 'frame.phtml', $aValueArray);
        }

        private function getNavFile() {
            switch ($_SESSION['user']->group) {
                case 1:
                    return 'nav_user.phtml';
                case 5:
                    return 'nav_contributor.phtml';
                case 9:
                case 10:
                    return 'nav_admin.phtml';
                default:
                    return 'nav_guest.phtml';
            };
        }
    }

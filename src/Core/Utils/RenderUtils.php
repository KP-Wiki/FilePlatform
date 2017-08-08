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

    use \Slim\Container;
    use \Slim\Http\Response;
    use \InvalidArgumentException;

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
		/** @var \Slim\Container $container The framework container */
        protected $container;

        /**
         * RenderUtils constructor.
         *
         * @param \Slim\Container The application controller.
         */
        public function __construct(Container &$aConstainer) {
            $this->container = $aConstainer;
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
         *
         * @return \Slim\Http\Response
         *
         * @throws InvalidArgumentException
         * @throws RuntimeException
         */
        public function render($aPageTitle, $aPageId, $aContentTemplate, Response &$aResponse, &$aValueArray) {
            $navFile                    = $this->getNavFile();
            $aValueArray['PageTitle']   = $aPageTitle;
            $aValueArray['PageID']      = $aPageId;
            $aValueArray['PageNav']     = $this->container->renderer->fetch($navFile, $aValueArray);
            $aValueArray['PageContent'] = $this->container->renderer->fetch($aContentTemplate, $aValueArray);
            $aValueArray['PageFooter']  = $this->container->renderer->fetch('footer.phtml', $aValueArray);

            return $this->container->renderer->render($aResponse, 'frame.phtml', $aValueArray);
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

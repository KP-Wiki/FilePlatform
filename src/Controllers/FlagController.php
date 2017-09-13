<?php
    /**
     * The central controller for the flag pages
     *
     * PHP version 7
     *
     * @package    MapPlatform
     * @subpackage Controllers
     * @author     Thimo Braker <thibmorozier@gmail.com>
     * @version    1.0.0
     * @since      First available since Release 1.0.0
     */
    namespace MapPlatform\Controllers;

    use \Slim\Http\Request;
    use \Slim\Http\Response;
    use \MapPlatform\Core;
    use \MapPlatform\AbstractClasses\PageController;
    use \DateTime;
    use \InvalidArgumentException;

    /**
     * FlagController page controller
     *
     * @package    MapPlatform
     * @subpackage Controllers
     * @author     Thimo Braker <thibmorozier@gmail.com>
     * @version    1.0.0
     * @since      First available since Release 1.0.0
     */
    class FlagController extends PageController
    {
        /**
         * FlagController invoker.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function __invoke(Request $request, Response $response, $args) {
            return $response;
        }

        /**
         * Show the default page.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function home(Request $request, Response $response, $args) {
            return $response;
        }

        /**
         * Show the flag collection page.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function getFlags(Request $request, Response $response, $args) {
            $this->container->logger->info("MapPlatform '/admin_flags' route");
            $this->container->security->checkRememberMe();

            if (($_SESSION['user']->id == -1) || ($_SESSION['user']->group <= 3)) {
                $response->getBody()->write('Taking you back to the homepage');

                return $response->withAddedHeader('Refresh', '1; url=/home');
            } else {
                $pageTitle            = 'Map Details';
                $pageID               = 0;
                $contentTemplate      = 'flags.phtml';
                $values['PageCrumbs'] = "<ol class=\"breadcrumb\">" . PHP_EOL .
                                        "    <li><a href=\"/home\">Home</a></li>" . PHP_EOL .
                                        "    <li class=\"active\">Map Details</li>" . PHP_EOL .
                                        "</ol>";
                return $this->container->renderUtils->render($pageTitle, $pageID, $contentTemplate, $response, $values);
            };
        }
    }

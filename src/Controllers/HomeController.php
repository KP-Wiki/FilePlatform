<?php
    /**
     * The central controller for the home page
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

    use \InvalidArgumentException;
    use \Slim\Http\Request;
    use \Slim\Http\Response;
    use \MapPlatform\Core;
    use \MapPlatform\AbstractClasses\PageController;

    /**
     * Home page controller
     *
     * @package    MapPlatform
     * @subpackage Controllers
     * @author     Thimo Braker <thibmorozier@gmail.com>
     * @version    1.0.0
     * @since      First available since Release 1.0.0
     */
    class HomeController extends PageController
    {
        /**
         * HomeController invoker.
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
            $this->container->logger->info("MapPlatform '/" . (Empty($args['catchall']) ? "" : $args['catchall']) . "' route");
            $this->container->security->checkRememberMe();

            $pageTitle            = 'Home Page';
            $pageID               = 0;
            $contentTemplate      = 'index.phtml';
            $values['PageCrumbs'] = "<ol class=\"breadcrumb\">" . PHP_EOL .
                                    "    <li class=\"active\">Home</li>" . PHP_EOL .
                                    "</ol>";

            return $this->container->renderUtils->render($pageTitle, $pageID, $contentTemplate, $response, $values);
        }

        /**
         * Show the about page.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function about(Request $request, Response $response, $args) {
            $this->container->logger->info("MapPlatform '/about" . (Empty($args['catchall']) ? "" : "/" . $args['catchall']) . "' route");
            $this->container->security->checkRememberMe();

            $route                = $request->getAttribute('route');
            $pageTitle            = 'About Map Platform';
            $pageID               = 1;
            $contentTemplate      = 'about.phtml';
            $values['PageCrumbs'] = "<ol class=\"breadcrumb\">" . PHP_EOL .
                                    "    <li class=\"active\">About</li>" . PHP_EOL .
                                    "</ol>";
            $values['request']    = [
                'route' => [
                    'name' => $route->getName(),
                    'uri' => $request->getUri(),
                    'arguments' => $route->getArguments()
                ]
            ];

            return $this->container->renderUtils->render($pageTitle, $pageID, $contentTemplate, $response, $values);
        }
    }

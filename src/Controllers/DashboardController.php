<?php
    /**
     * The central controller for the dashboard page
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
     * Dashboard page controller
     *
     * @package    MapPlatform
     * @subpackage Controllers
     * @author     Thimo Braker <thibmorozier@gmail.com>
     * @version    1.0.0
     * @since      First available since Release 1.0.0
     */
    final class DashboardController extends PageController
    {
        /**
         * DashboardController invoker.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function __invoke(Request $request, Response $response, $args)
        {
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
        public function home(Request $request, Response $response, $args)
        {
            $this->container->logger->info("MapPlatform '/dashboard' route");
            $this->container->security->checkRememberMe();

            if ($_SESSION['user']->id == -1) {
                $response->getBody()->write('Taking you back to the homepage');
                return $response->withAddedHeader('Refresh', '1; url=/home');
            } else {
                $pageTitle = 'Dashboard';
                $pageID = 3;
                $contentTemplate = 'dashboard.phtml';
                $values['PageCrumbs'] = '<ol class="breadcrumb">
    <li class="active">Dashboard</li>
</ol>';
                return $this->container->renderUtils->render($pageTitle, $pageID, $contentTemplate, $response, $values);
            }
        }
    }

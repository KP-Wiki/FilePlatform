<?php
    /**
     * The central controller for A.A.A.
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
     * A.A.A. controller
     *
     * @package    MapPlatform
     * @subpackage Controllers
     * @author     Thimo Braker <thibmorozier@gmail.com>
     * @version    1.0.0
     * @since      First available since Release 1.0.0
     */
    final class AAAController extends PageController
    {
        /**
         * AAAController invoker.
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
            return $response;
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
        public function register(Request $request, Response $response, $args)
        {
            $this->container->logger->info("MapPlatform '/register' route");
            $data = $request->getParsedBody();
            $result = $this->container->security->register($data);
            $pageTitle = 'Register';
            $pageID = 0;
            $contentTemplate = 'result.phtml';
            $values['PageCrumbs'] = '<ol class="breadcrumb">
    <li><a href="/home">Home</a></li>
    <li class="active">Register</li>
</ol>';
            $values['resultType'] = $result['status'];
            $values['resultMessage'] = $result['message'];
            return $this->container->renderUtils->render($pageTitle, $pageID, $contentTemplate, $response, $values)
                ->withAddedHeader('Refresh', '5; url=/home');
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
        public function login(Request $request, Response $response, $args)
        {
            $this->container->logger->info("MapPlatform '/login' route");
            $data = $request->getParsedBody();
            $result = $this->container->security->login($data);
            $pageTitle = 'Login';
            $pageID = 0;
            $contentTemplate = 'result.phtml';
            $values['PageCrumbs'] = '<ol class="breadcrumb">
    <li><a href="/home">Home</a></li>
    <li class="active">Login</li>
</ol>';
            $values['resultType'] = $result['status'];
            $values['resultMessage'] = $result['message'];
            return $this->container->renderUtils->render($pageTitle, $pageID, $contentTemplate, $response, $values)
                ->withAddedHeader('Refresh', '5; url=/home');
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
        public function logout(Request $request, Response $response, $args)
        {
            $this->container->logger->info("MapPlatform '/register' route");
            $result = $this->container->security->logout();
            $pageTitle = 'Logout';
            $pageID = 0;
            $contentTemplate = 'result.phtml';
            $values['PageCrumbs'] = '<ol class="breadcrumb">
    <li><a href="/home">Home</a></li>
    <li class="active">Logout</li>
</ol>';
            $values['resultType'] = $result['status'];
            $values['resultMessage'] = $result['message'];
            return $this->container->renderUtils->render($pageTitle, $pageID, $contentTemplate, $response, $values)
                ->withAddedHeader('Refresh', '5; url=/home');
        }
    }

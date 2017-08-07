<?php
    /**
     * The central controller for all rating requests
     *
     * PHP version 7
     *
     * @package    MapPlatform
     * @subpackage Controllers\Api
     * @author     Thimo Braker <thibmorozier@gmail.com>
     * @version    1.0.0
     * @since      First available since Release 1.0.0
     */
    namespace MapPlatform\Controllers\Api;
    
    use InvalidArgumentException;
    use Slim\Http\Request;
    use Slim\Http\Response;
    use MapPlatform\Core;
    use MapPlatform\AbstractClasses\ApiController;

    /**
     * Rating controller
     *
     * @package    MapPlatform
     * @subpackage Controllers\Api
     * @author     Thimo Braker <thibmorozier@gmail.com>
     * @version    1.0.0
     * @since      First available since Release 1.0.0
     */
    class RatingController extends ApiController
    {
        /**
         * RatingController invoker.
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
         * RatingController default page.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function home(Request $request, Response $response, $args) {
            $this->container->logger->info("ManagementTools '/api/v1/testscript" . (Empty($args['catchall']) ? "" : "/" . $args['catchall']) . "' route");
            $resultArr   = array();

            return $response->withJson($resultArr, 200, JSON_PRETTY_PRINT);
        }
    }

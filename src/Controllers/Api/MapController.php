<?php
    /**
     * The central controller for all map features
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
    
    use \InvalidArgumentException;
    use \Slim\Http\Request;
    use \Slim\Http\Response;
    use \MapPlatform\Core;
    use \MapPlatform\AbstractClasses\ApiController;

    /**
     * Map controller
     *
     * @package    MapPlatform
     * @subpackage Controllers\Api
     * @author     Thimo Braker <thibmorozier@gmail.com>
     * @version    1.0.0
     * @since      First available since Release 1.0.0
     */
    class MapController extends ApiController
    {
        /**
         * MapController invoker.
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
         * MapController Get all maps.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function getAllMaps(Request $request, Response $response, $args) {
            $this->container->logger->info("MapPlatform '/api/v1/maps' route");
            $resultArr = array();

            return $response->withJson($resultArr, 200, JSON_PRETTY_PRINT);
        }

        /**
         * MapController Get a specific map.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function getMap(Request $request, Response $response, $args) {
            $this->container->logger->info("MapPlatform '/api/v1/maps' route");
            $resultArr = array();

            return $response->withJson($resultArr, 200, JSON_PRETTY_PRINT);
        }

        /**
         * MapController Get a map by user ID.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function getMapsByUser(Request $request, Response $response, $args) {
            $this->container->logger->info("MapPlatform '/api/v1/maps/user/" . $args['userId'] . "' route");
            $userId = filter_var($args['userId'], FILTER_SANITIZE_NUMBER_INT);
            $mapListItems = null;
            $resultArr = array();

            if ($userId === null || $userId <= 0)
                throw new Exception('Ilegal user ID : ' . $userId);

            

            return $response->withJson($resultArr, 200, JSON_PRETTY_PRINT);
        }

        /**
         * MapController Add map function.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function addMap(Request $request, Response $response, $args) {
            $this->container->logger->info("MapPlatform '/api/v1/maps' route");
            $data = $request->getParsedBody();
            $resultArr = array();

            return $response->withJson($resultArr, 200, JSON_PRETTY_PRINT);
        }

        /**
         * MapController Update map function.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function updateMap(Request $request, Response $response, $args) {
            $this->container->logger->info("MapPlatform '/api/v1/maps' route");
            $data = $request->getParsedBody();
            $resultArr = array();

            return $response->withJson($resultArr, 200, JSON_PRETTY_PRINT);
        }

        /**
         * MapController Delete map function.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function deleteMap(Request $request, Response $response, $args) {
            $this->container->logger->info("MapPlatform '/api/v1/maps' route");
            $data = $request->getParsedBody();
            $resultArr = array();

            return $response->withJson($resultArr, 200, JSON_PRETTY_PRINT);
        }
    }
